<?php

namespace App\Services\QuestImport;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Support\QuestDescriptionSections;
use App\Support\QuestDifficulty;
use App\Support\QuestImportFieldResolver;
use App\Support\QuestTier;
use App\Support\SkillKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class QuestImportItemEnricher
{
    private const LOG_PREFIX = '[quest-import-preview]';

    public function __construct(
        private readonly QuestImportToolResolver $toolResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $item
     * @param  bool  $preservePublish  true のときリクエストの公開状態を優先（反映時）
     * @return array<string, mixed>
     */
    public function enrichItem(
        array $item,
        bool $preservePublish = false,
        ?Collection $toolCodes = null,
        bool $logUnchangedDiff = false,
        ?string $defaultQuestTier = null,
    ): array {
        $match = $this->findExisting((string) ($item['kind'] ?? ''), $item);
        $action = $match['id'] !== null ? 'update' : 'create';

        if ($preservePublish && array_key_exists('isPublished', $item)) {
            $isPublished = (bool) $item['isPublished'];
        } elseif ($action === 'update' && $match['id'] !== null) {
            $isPublished = $this->fetchPublishedState((string) $item['kind'], $match['id']);
        } else {
            $isPublished = false;
        }

        $enriched = [
            ...$item,
            'action' => $action,
            'existingId' => $match['id'],
            'isPublished' => $isPublished,
        ];

        if ($action === 'update' && $match['id'] !== null) {
            $diffs = $this->collectUnchangedDiffs($enriched, $match['id'], $toolCodes);

            if ($diffs === []) {
                $enriched['action'] = 'unchanged';
            } elseif ($logUnchangedDiff) {
                $this->logUnchangedDiff($enriched, $match['id'], $diffs);
            }
        } elseif (
            $action === 'create'
            && ($item['kind'] ?? '') === 'child_quest'
            && ! array_key_exists('questTier', $item)
        ) {
            $enriched['questTier'] = $defaultQuestTier ?? QuestTier::LOW;
        }

        return $enriched;
    }

    /**
     * @return array{id: int|null}
     */
    private function findExisting(string $kind, array $item): array
    {
        if (isset($item['id']) && is_numeric($item['id']) && (int) $item['id'] > 0) {
            return ['id' => (int) $item['id']];
        }

        if (isset($item['existingId']) && is_numeric($item['existingId']) && (int) $item['existingId'] > 0) {
            return ['id' => (int) $item['existingId']];
        }

        return match ($kind) {
            'personal_unit' => [
                'id' => QuestUnit::query()->where('title', (string) ($item['title'] ?? ''))->value('id'),
            ],
            'child_quest' => $this->findChildQuestId($item),
            'team_quest', 'special_quest' => [
                'id' => Quest::query()
                    ->where('type', $kind === 'team_quest' ? Quest::TYPE_TEAM : Quest::TYPE_SPECIAL)
                    ->whereNull('quest_unit_id')
                    ->where('title', (string) ($item['title'] ?? ''))
                    ->value('id'),
            ],
            default => ['id' => null],
        };
    }

    /**
     * @return array{id: int|null}
     */
    private function findChildQuestId(array $item): array
    {
        $unitTitle = (string) ($item['unitTitle'] ?? '');
        $questTitle = (string) ($item['title'] ?? '');

        if ($unitTitle === '' || $questTitle === '') {
            return ['id' => null];
        }

        $unitId = QuestUnit::query()->where('title', $unitTitle)->value('id');
        if ($unitId === null) {
            return ['id' => null];
        }

        $id = Quest::query()
            ->where('quest_unit_id', $unitId)
            ->where('type', Quest::TYPE_PERSONAL)
            ->where('title', $questTitle)
            ->value('id');

        return ['id' => $id !== null ? (int) $id : null];
    }

    private function fetchPublishedState(string $kind, int $id): bool
    {
        return match ($kind) {
            'personal_unit' => (bool) QuestUnit::query()->whereKey($id)->value('is_published'),
            'child_quest', 'team_quest', 'special_quest' => (bool) Quest::query()->whereKey($id)->value('is_published'),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function collectUnchangedDiffs(array $item, int $existingId, ?Collection $toolCodes): array
    {
        return match ((string) ($item['kind'] ?? '')) {
            'personal_unit' => $this->collectPersonalUnitDiffs($item, $existingId),
            'child_quest' => $this->collectChildQuestDiffs($item, $existingId, $toolCodes),
            'team_quest', 'special_quest' => $this->collectBoardQuestDiffs($item, $existingId),
            default => ['kind' => ['incoming' => $item['kind'] ?? null, 'existing' => null]],
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function collectPersonalUnitDiffs(array $item, int $existingId): array
    {
        $diffs = [];
        $unit = QuestUnit::query()->whereKey($existingId)->first();

        if ($unit === null) {
            return ['record' => ['incoming' => $existingId, 'existing' => null]];
        }

        $sortOrder = $this->resolveComparableSortOrder((int) ($item['sortOrder'] ?? 0), (int) $unit->sort_order);
        $this->addScalarDiff($diffs, 'sortOrder', $sortOrder, (int) $unit->sort_order);
        $this->addScalarDiff(
            $diffs,
            'isPublished',
            (bool) ($item['isPublished'] ?? false),
            (bool) $unit->is_published,
        );

        return $diffs;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function collectChildQuestDiffs(array $item, int $existingId, ?Collection $toolCodes): array
    {
        $diffs = [];
        $quest = Quest::query()->with(['rewards', 'tools'])->whereKey($existingId)->first();

        if ($quest === null) {
            return ['record' => ['incoming' => $existingId, 'existing' => null]];
        }

        $unitTitle = trim((string) ($item['unitTitle'] ?? ''));
        $unitId = QuestUnit::query()->where('title', $unitTitle)->value('id');

        if ($unitId === null) {
            $diffs['unitTitle'] = ['incoming' => $unitTitle, 'existing' => null, 'reason' => 'unit_not_found'];
        } elseif ((int) $quest->quest_unit_id !== (int) $unitId) {
            $diffs['unitTitle'] = [
                'incoming' => $unitTitle,
                'incomingUnitId' => (int) $unitId,
                'existingUnitId' => (int) $quest->quest_unit_id,
            ];
        }

        $sortOrder = $this->resolveComparableSortOrder((int) ($item['sortOrder'] ?? 0), (int) $quest->sort_order);
        $toolCodes ??= $this->toolResolver->loadToolNameMap();
        $existingDifficulty = $quest->difficulty !== null ? (int) $quest->difficulty : null;
        $difficulty = QuestImportFieldResolver::resolveDifficulty($item['difficulty'] ?? null, $existingDifficulty);
        $existingTier = QuestTier::resolve(
            $quest->quest_tier,
            $quest->unlock_level !== null ? (int) $quest->unlock_level : null,
        );
        $questTier = QuestImportFieldResolver::resolveQuestTier($item['questTier'] ?? null, $quest);

        $this->addTextDiff($diffs, 'title', (string) ($item['title'] ?? ''), (string) $quest->title);

        $sectionDiffs = QuestDescriptionSections::diffSections(
            (string) ($item['description'] ?? ''),
            (string) ($item['clearCondition'] ?? ''),
            (string) ($quest->description ?? ''),
            (string) ($quest->clear_condition ?? ''),
        );

        if ($sectionDiffs !== []) {
            $diffs['descriptionSections'] = $this->summarizeSectionDiffs($sectionDiffs);
        }

        $this->addScalarDiff($diffs, 'difficulty', $difficulty, $existingDifficulty);
        $this->addScalarDiff(
            $diffs,
            'experiencePoints',
            QuestDifficulty::experiencePoints($difficulty),
            (int) ($quest->experience_points ?? 0),
        );

        $incomingToolIds = $this->resolveIncomingToolIds($item, $toolCodes);
        $existingToolIds = $this->resolveExistingToolIds($quest);

        if ($incomingToolIds !== $existingToolIds) {
            $diffs['tools'] = [
                'incomingToolCode' => trim((string) ($item['toolCode'] ?? '')),
                'incomingToolIds' => $incomingToolIds,
                'existingToolIds' => $existingToolIds,
            ];
        }

        $this->addScalarDiff($diffs, 'sortOrder', $sortOrder, (int) $quest->sort_order);
        $this->addScalarDiff(
            $diffs,
            'isRequired',
            (bool) ($item['isRequired'] ?? true),
            (bool) $quest->is_required,
        );
        $this->addScalarDiff(
            $diffs,
            'isPublished',
            (bool) ($item['isPublished'] ?? false),
            (bool) $quest->is_published,
        );
        $this->addScalarDiff($diffs, 'questTier', $questTier, $existingTier);

        $incomingSkills = $this->normalizeIncomingSkillGrants($item['skillGrants'] ?? []);
        $existingSkills = $this->normalizeExistingSkillGrants($quest->rewards);

        if ($incomingSkills !== $existingSkills) {
            $diffs['skillGrants'] = [
                'incoming' => $incomingSkills,
                'existing' => $existingSkills,
            ];
        }

        return $diffs;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function collectBoardQuestDiffs(array $item, int $existingId): array
    {
        $diffs = [];
        $quest = Quest::query()->with('rewards')->whereKey($existingId)->first();

        if ($quest === null) {
            return ['record' => ['incoming' => $existingId, 'existing' => null]];
        }

        $sortOrder = $this->resolveComparableSortOrder((int) ($item['sortOrder'] ?? 0), (int) $quest->sort_order);
        $unlockLevel = array_key_exists('unlockLevel', $item) && $item['unlockLevel'] !== null && $item['unlockLevel'] !== ''
            ? (int) $item['unlockLevel']
            : null;
        $badgeLabel = ($item['badgeLabel'] ?? null) !== null && $item['badgeLabel'] !== ''
            ? (string) $item['badgeLabel']
            : null;
        $existingDifficulty = $quest->difficulty !== null ? (int) $quest->difficulty : null;
        $difficulty = QuestImportFieldResolver::resolveDifficulty($item['difficulty'] ?? null, $existingDifficulty);

        $this->addTextDiff($diffs, 'title', (string) ($item['title'] ?? ''), (string) $quest->title);

        $sectionDiffs = QuestDescriptionSections::diffSections(
            (string) ($item['description'] ?? ''),
            (string) ($item['clearCondition'] ?? ''),
            (string) ($quest->description ?? ''),
            (string) ($quest->clear_condition ?? ''),
        );

        if ($sectionDiffs !== []) {
            $diffs['descriptionSections'] = $this->summarizeSectionDiffs($sectionDiffs);
        }

        $this->addTextDiff($diffs, 'rewardText', (string) ($item['rewardText'] ?? ''), (string) ($quest->reward_text ?? ''));
        $this->addTextDiff($diffs, 'badgeLabel', (string) ($badgeLabel ?? ''), (string) ($quest->badge_label ?? ''));
        $this->addScalarDiff(
            $diffs,
            'isRequired',
            (bool) ($item['isRequired'] ?? true),
            (bool) $quest->is_required,
        );
        $this->addScalarDiff(
            $diffs,
            'unlockLevel',
            $unlockLevel,
            $quest->unlock_level !== null ? (int) $quest->unlock_level : null,
        );
        $this->addScalarDiff($diffs, 'difficulty', $difficulty, $existingDifficulty);
        $this->addScalarDiff(
            $diffs,
            'experiencePoints',
            QuestDifficulty::experiencePoints($difficulty),
            (int) ($quest->experience_points ?? 0),
        );
        $this->addScalarDiff($diffs, 'sortOrder', $sortOrder, (int) $quest->sort_order);
        $this->addScalarDiff(
            $diffs,
            'isPublished',
            (bool) ($item['isPublished'] ?? false),
            (bool) $quest->is_published,
        );

        $incomingSkills = $this->normalizeIncomingSkillGrants($item['skillGrants'] ?? []);
        $existingSkills = $this->normalizeExistingSkillGrants($quest->rewards);

        if ($incomingSkills !== $existingSkills) {
            $diffs['skillGrants'] = [
                'incoming' => $incomingSkills,
                'existing' => $existingSkills,
            ];
        }

        return $diffs;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $diffs
     */
    private function logUnchangedDiff(array $item, int $existingId, array $diffs): void
    {
        Log::info(self::LOG_PREFIX.' marked as update', [
            'kind' => $item['kind'] ?? null,
            'existingId' => $existingId,
            'title' => $item['title'] ?? null,
            'unitTitle' => $item['unitTitle'] ?? null,
            'csvNo' => $item['csvNo'] ?? null,
            'diffFields' => array_keys($diffs),
            'diffs' => $diffs,
        ]);
    }

    private function resolveComparableSortOrder(int $incoming, int $existing): int
    {
        return $incoming <= 0 ? $existing : $incoming;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  Collection<string, int>  $toolCodes
     * @return list<int>
     */
    private function resolveIncomingToolIds(array $item, Collection $toolCodes): array
    {
        $toolRef = trim((string) ($item['toolCode'] ?? ''));

        return $toolRef !== ''
            ? $this->toolResolver->resolveToolIds($toolRef, $toolCodes)
            : [];
    }

    /**
     * @return list<int>
     */
    private function resolveExistingToolIds(Quest $quest): array
    {
        $existingIds = $quest->tools
            ->sortBy(fn (mixed $tool): int => (int) ($tool->pivot->sort_order ?? 0))
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        if ($existingIds === [] && $quest->tool_id !== null) {
            return [(int) $quest->tool_id];
        }

        return $existingIds;
    }

    /**
     * @param  array<string, mixed>  $diffs
     */
    private function addScalarDiff(array &$diffs, string $field, mixed $incoming, mixed $existing): void
    {
        if ($incoming !== $existing) {
            $diffs[$field] = [
                'incoming' => $incoming,
                'existing' => $existing,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $diffs
     */
    private function addTextDiff(array &$diffs, string $field, string $incoming, string $existing): void
    {
        if (! $this->sameText($incoming, $existing)) {
            $diffs[$field] = [
                'incoming' => $this->summarizeText($incoming),
                'existing' => $this->summarizeText($existing),
            ];
        }
    }

    /**
     * @param  array<string, array{incoming: string, existing: string}>  $sectionDiffs
     * @return array<string, array{incoming: array{text: string, length: int}, existing: array{text: string, length: int}}>
     */
    private function summarizeSectionDiffs(array $sectionDiffs): array
    {
        $summary = [];

        foreach ($sectionDiffs as $section => $values) {
            $summary[$section] = [
                'incoming' => $this->summarizeText($values['incoming']),
                'existing' => $this->summarizeText($values['existing']),
            ];
        }

        return $summary;
    }

    /**
     * @return array{text: string, length: int}
     */
    private function summarizeText(string $text): array
    {
        $trimmed = trim($text);
        $maxLength = 120;

        if (mb_strlen($trimmed) <= $maxLength) {
            return [
                'text' => $trimmed,
                'length' => mb_strlen($trimmed),
            ];
        }

        return [
            'text' => mb_substr($trimmed, 0, $maxLength).'…',
            'length' => mb_strlen($trimmed),
        ];
    }

    private function sameText(string $left, string $right): bool
    {
        return trim($left) === trim($right);
    }

    /**
     * @return list<string>
     */
    private function normalizeIncomingSkillGrants(mixed $incoming): array
    {
        if (! is_array($incoming)) {
            return [];
        }

        return SkillKeys::normalizeList($incoming);
    }

    /**
     * @return list<string>
     */
    private function normalizeExistingSkillGrants(mixed $existing): array
    {
        $skills = [];

        foreach ($existing ?? [] as $reward) {
            if (! is_array($reward) && ! is_object($reward)) {
                continue;
            }

            $skill = is_array($reward)
                ? (string) ($reward['skill'] ?? $reward['stat'] ?? '')
                : (string) ($reward->stat ?? '');

            if ($skill !== '') {
                $skills[] = $skill;
            }
        }

        return SkillKeys::normalizeList($skills);
    }
}
