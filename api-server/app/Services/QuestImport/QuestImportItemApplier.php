<?php

namespace App\Services\QuestImport;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Services\QuestSkillGrantSync;
use App\Support\QuestDifficulty;
use App\Support\QuestTier;
use Illuminate\Support\Collection;

class QuestImportItemApplier
{
    public function __construct(
        private readonly QuestImportToolResolver $toolResolver,
        private readonly QuestSkillGrantSync $questSkillGrantSync,
    ) {}

    /**
     * @param  array<string, mixed>  $item
     * @param  Collection<string, int>  $toolCodes
     * @return array<string, mixed>
     */
    public function applyItem(array $item, Collection $toolCodes): array
    {
        if (($item['action'] ?? '') === 'unchanged') {
            return [
                'kind' => $item['kind'],
                'action' => 'unchanged',
                'id' => $item['existingId'] ?? null,
                'title' => $item['title'] ?? '',
            ];
        }

        return match ((string) $item['kind']) {
            'personal_unit' => $this->applyPersonalUnit($item),
            'child_quest' => $this->applyChildQuest($item, $toolCodes),
            'team_quest', 'special_quest' => $this->applyBoardQuest($item),
            default => ['kind' => $item['kind'], 'status' => 'skipped'],
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function applyPersonalUnit(array $item): array
    {
        $existingId = $item['existingId'] ?? null;
        $attributes = [
            'description' => '',
            'reward_text' => null,
            'sort_order' => $this->resolveSortOrder(
                (int) ($item['sortOrder'] ?? 0),
                $existingId !== null ? (int) QuestUnit::query()->whereKey($existingId)->value('sort_order') : null,
                fn (): int => ((int) QuestUnit::query()->max('sort_order')) + 1,
            ),
        ];

        $unit = QuestUnit::query()->updateOrCreate(
            ['title' => (string) $item['title']],
            $attributes,
        );

        return [
            'kind' => 'personal_unit',
            'action' => $item['action'] ?? 'create',
            'id' => $unit->id,
            'title' => $unit->title,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  Collection<string, int>  $toolCodes
     * @return array<string, mixed>
     */
    private function applyChildQuest(array $item, Collection $toolCodes): array
    {
        $unit = QuestUnit::query()->where('title', (string) $item['unitTitle'])->firstOrFail();
        $toolRef = trim((string) ($item['toolCode'] ?? ''));
        $toolId = $toolRef !== '' ? $this->toolResolver->resolveToolId($toolRef, $toolCodes) : null;
        $existingId = $item['existingId'] ?? null;

        $difficulty = QuestDifficulty::normalize($item['difficulty'] ?? null);

        $attributes = [
            'title' => (string) $item['title'],
            'description' => (string) ($item['description'] ?? ''),
            'clear_condition' => (string) ($item['clearCondition'] ?? ''),
            'difficulty' => $difficulty,
            'experience_points' => QuestDifficulty::experiencePoints($difficulty),
            'tool_id' => $toolId,
            'sort_order' => $this->resolveSortOrder(
                (int) ($item['sortOrder'] ?? 0),
                $existingId !== null ? (int) Quest::query()->whereKey($existingId)->value('sort_order') : null,
                fn (): int => ((int) $unit->quests()->max('sort_order')) + 1,
            ),
            'is_published' => true,
            'type' => Quest::TYPE_PERSONAL,
            'quest_unit_id' => $unit->id,
            'is_required' => (bool) ($item['isRequired'] ?? true),
            'reward_text' => null,
            'badge_label' => null,
            'brand_label' => null,
        ];
        QuestTier::applyToAttributes($attributes, $item['questTier'] ?? QuestTier::LOW);

        if ($existingId !== null) {
            $quest = Quest::query()->whereKey($existingId)->firstOrFail();
            $quest->update($attributes);
        } else {
            $quest = Quest::query()->create([
                ...$attributes,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(2)->toDateString(),
            ]);
        }

        $this->questSkillGrantSync->syncForQuest($quest, $item['skillGrants'] ?? []);

        return [
            'kind' => 'child_quest',
            'action' => $item['action'] ?? 'create',
            'id' => $quest->id,
            'title' => $quest->title,
            'unitId' => $unit->id,
            'unitTitle' => $unit->title,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function applyBoardQuest(array $item): array
    {
        $type = $item['kind'] === 'team_quest' ? Quest::TYPE_TEAM : Quest::TYPE_SPECIAL;
        $existingId = $item['existingId'] ?? null;

        $difficulty = QuestDifficulty::normalize($item['difficulty'] ?? null);

        $attributes = [
            'description' => (string) ($item['description'] ?? ''),
            'clear_condition' => (string) ($item['clearCondition'] ?? ''),
            'is_required' => (bool) ($item['isRequired'] ?? true),
            'unlock_level' => isset($item['unlockLevel']) ? (int) $item['unlockLevel'] : null,
            'reward_text' => (string) ($item['rewardText'] ?? ''),
            'badge_label' => ($item['badgeLabel'] ?? null) !== null && $item['badgeLabel'] !== ''
                ? (string) $item['badgeLabel']
                : null,
            'difficulty' => $difficulty,
            'experience_points' => QuestDifficulty::experiencePoints($difficulty),
            'sort_order' => $this->resolveSortOrder(
                (int) ($item['sortOrder'] ?? 0),
                $existingId !== null ? (int) Quest::query()->whereKey($existingId)->value('sort_order') : null,
                fn (): int => ((int) Quest::query()
                    ->where('type', $type)
                    ->whereNull('quest_unit_id')
                    ->max('sort_order')) + 1,
            ),
            'type' => $type,
            'quest_unit_id' => null,
            'tool_id' => null,
            'brand_label' => null,
        ];

        if ($existingId !== null) {
            $quest = Quest::query()->whereKey($existingId)->firstOrFail();
            $quest->update($attributes);
        } else {
            $quest = Quest::query()->create([
                ...$attributes,
                'title' => (string) $item['title'],
                'is_published' => false,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(2)->toDateString(),
            ]);
        }

        $this->questSkillGrantSync->syncForQuest($quest, $item['skillGrants'] ?? []);

        return [
            'kind' => $item['kind'],
            'action' => $item['action'] ?? 'create',
            'id' => $quest->id,
            'title' => $quest->title,
        ];
    }

    private function resolveSortOrder(int $incoming, ?int $existing, callable $fallback): int
    {
        if ($incoming > 0) {
            return $incoming;
        }

        if ($existing !== null) {
            return $existing;
        }

        return $fallback();
    }
}
