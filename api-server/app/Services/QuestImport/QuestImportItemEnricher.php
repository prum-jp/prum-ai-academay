<?php

namespace App\Services\QuestImport;

use App\Models\Quest;
use App\Models\QuestUnit;
use Illuminate\Support\Collection;

class QuestImportItemEnricher
{
    public function __construct(
        private readonly QuestImportToolResolver $toolResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $item
     * @param  bool  $preservePublish  true のときリクエストの公開状態を優先（反映時）
     * @return array<string, mixed>
     */
    public function enrichItem(array $item, bool $preservePublish = false, ?Collection $toolCodes = null): array
    {
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

        if ($action === 'update' && $match['id'] !== null && $this->isUnchanged($enriched, $match['id'], $toolCodes)) {
            $enriched['action'] = 'unchanged';
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
     */
    private function isUnchanged(array $item, int $existingId, ?Collection $toolCodes): bool
    {
        return match ((string) ($item['kind'] ?? '')) {
            'personal_unit' => $this->isPersonalUnitUnchanged($item, $existingId),
            'child_quest' => $this->isChildQuestUnchanged($item, $existingId, $toolCodes),
            'team_quest', 'special_quest' => $this->isBoardQuestUnchanged($item, $existingId),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isPersonalUnitUnchanged(array $item, int $existingId): bool
    {
        $unit = QuestUnit::query()->with('rewards')->whereKey($existingId)->first();
        if ($unit === null) {
            return false;
        }

        $sortOrder = $this->resolveComparableSortOrder((int) ($item['sortOrder'] ?? 0), (int) $unit->sort_order);

        if (! $this->sameText((string) ($item['description'] ?? ''), (string) ($unit->description ?? ''))) {
            return false;
        }

        if (! $this->sameText((string) ($item['rewardText'] ?? ''), (string) ($unit->reward_text ?? ''))) {
            return false;
        }

        if ($sortOrder !== (int) $unit->sort_order) {
            return false;
        }

        if ((bool) ($item['isPublished'] ?? false) !== (bool) $unit->is_published) {
            return false;
        }

        return $this->sameRewards($item['rewards'] ?? [], $unit->rewards);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isChildQuestUnchanged(array $item, int $existingId, ?Collection $toolCodes): bool
    {
        $quest = Quest::query()->whereKey($existingId)->first();
        if ($quest === null) {
            return false;
        }

        $unitTitle = trim((string) ($item['unitTitle'] ?? ''));
        $unitId = QuestUnit::query()->where('title', $unitTitle)->value('id');
        if ($unitId === null || (int) $quest->quest_unit_id !== (int) $unitId) {
            return false;
        }

        $sortOrder = $this->resolveComparableSortOrder((int) ($item['sortOrder'] ?? 0), (int) $quest->sort_order);
        $toolCodes ??= $this->toolResolver->loadToolCodeMap();
        $toolRef = trim((string) ($item['toolCode'] ?? ''));
        $toolId = $toolRef !== '' ? $this->toolResolver->resolveToolId($toolRef, $toolCodes) : null;
        $estimatedDuration = $this->normalizeEstimatedDuration($item['estimatedDuration'] ?? null);

        return $this->sameText((string) ($item['title'] ?? ''), (string) $quest->title)
            && $this->sameText((string) ($item['description'] ?? ''), (string) ($quest->description ?? ''))
            && $this->sameText((string) ($item['clearCondition'] ?? ''), (string) ($quest->clear_condition ?? ''))
            && $this->sameText((string) ($estimatedDuration ?? ''), (string) ($quest->estimated_duration ?? ''))
            && $toolId === ($quest->tool_id !== null ? (int) $quest->tool_id : null)
            && $sortOrder === (int) $quest->sort_order
            && (bool) ($item['isPublished'] ?? false) === (bool) $quest->is_published;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isBoardQuestUnchanged(array $item, int $existingId): bool
    {
        $quest = Quest::query()->with('rewards')->whereKey($existingId)->first();
        if ($quest === null) {
            return false;
        }

        $sortOrder = $this->resolveComparableSortOrder((int) ($item['sortOrder'] ?? 0), (int) $quest->sort_order);
        $unlockLevel = array_key_exists('unlockLevel', $item) && $item['unlockLevel'] !== null && $item['unlockLevel'] !== ''
            ? (int) $item['unlockLevel']
            : null;
        $badgeLabel = ($item['badgeLabel'] ?? null) !== null && $item['badgeLabel'] !== ''
            ? (string) $item['badgeLabel']
            : null;

        return $this->sameText((string) ($item['title'] ?? ''), (string) $quest->title)
            && $this->sameText((string) ($item['description'] ?? ''), (string) ($quest->description ?? ''))
            && $this->sameText((string) ($item['clearCondition'] ?? ''), (string) ($quest->clear_condition ?? ''))
            && $this->sameText((string) ($item['rewardText'] ?? ''), (string) ($quest->reward_text ?? ''))
            && $this->sameText((string) ($badgeLabel ?? ''), (string) ($quest->badge_label ?? ''))
            && (bool) ($item['isRequired'] ?? true) === (bool) $quest->is_required
            && $unlockLevel === ($quest->unlock_level !== null ? (int) $quest->unlock_level : null)
            && $sortOrder === (int) $quest->sort_order
            && (bool) ($item['isPublished'] ?? false) === (bool) $quest->is_published
            && $this->sameRewards($item['rewards'] ?? [], $quest->rewards);
    }

    private function resolveComparableSortOrder(int $incoming, int $existing): int
    {
        return $incoming <= 0 ? $existing : $incoming;
    }

    private function normalizeEstimatedDuration(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function sameText(string $left, string $right): bool
    {
        return trim($left) === trim($right);
    }

    /**
     * @param  mixed  $incoming
     * @param  \Illuminate\Support\Collection<int, \App\Models\QuestReward|\App\Models\QuestUnitReward>|iterable<mixed>  $existing
     */
    private function sameRewards(mixed $incoming, mixed $existing): bool
    {
        $normalize = function (mixed $rewards): array {
            $rows = [];

            foreach ($rewards ?? [] as $reward) {
                if (! is_array($reward) && ! is_object($reward)) {
                    continue;
                }

                $stat = is_array($reward)
                    ? (string) ($reward['stat'] ?? '')
                    : (string) ($reward->stat ?? '');
                $points = is_array($reward)
                    ? (int) ($reward['points'] ?? 0)
                    : (int) ($reward->points ?? 0);

                if ($stat === '') {
                    continue;
                }

                $rows[] = $stat.':'.$points;
            }

            sort($rows);

            return $rows;
        };

        return $normalize($incoming) === $normalize($existing);
    }
}
