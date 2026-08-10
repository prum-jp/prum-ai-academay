<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Support\QuestTier;
use Illuminate\Support\Facades\DB;

class MentorQuestUnitRegistrar
{
    public function __construct(
        private readonly QuestSkillGrantSync $questSkillGrantSync,
        private readonly QuestToolSync $questToolSync,
    ) {}

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     quests?: list<array{id?: int|null, title: string, description?: string|null, clearCondition?: string|null, toolId?: int|null, sortOrder?: int|null, difficulty?: int|null, experiencePoints?: int|null, skillGrants?: list<string>, questTier?: string|null}>
     * }  $payload
     */
    public function register(array $payload): QuestUnit
    {
        return DB::transaction(function () use ($payload): QuestUnit {
            $unit = QuestUnit::query()->create([
                'title' => $payload['title'],
                'description' => '',
                'reward_text' => null,
                'sort_order' => ((int) QuestUnit::query()->max('sort_order')) + 1,
            ]);

            $this->syncChildQuests($unit, $payload['quests'] ?? []);

            return $unit->loadCount('quests');
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     quests?: list<array{id?: int|null, title: string, description?: string|null, clearCondition?: string|null, toolId?: int|null, sortOrder?: int|null, difficulty?: int|null, experiencePoints?: int|null, skillGrants?: list<string>, questTier?: string|null}>
     * }  $payload
     */
    public function update(QuestUnit $unit, array $payload): QuestUnit
    {
        return DB::transaction(function () use ($unit, $payload): QuestUnit {
            $unit->update([
                'title' => $payload['title'],
                'description' => '',
                'reward_text' => null,
            ]);

            $this->syncChildQuests($unit, $payload['quests'] ?? []);

            return $unit->fresh()->loadCount('quests');
        });
    }

    public function delete(QuestUnit $unit): void
    {
        DB::transaction(function () use ($unit): void {
            $unit->quests()->get()->each(fn (Quest $quest) => $quest->delete());
            $unit->rewards()->delete();
            $unit->delete();
        });
    }

    /**
     * @param  list<int>  $unitIds
     */
    public function reorder(array $unitIds): void
    {
        DB::transaction(function () use ($unitIds): void {
            foreach ($unitIds as $index => $unitId) {
                QuestUnit::query()
                    ->whereKey($unitId)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    /**
     * @param  list<array{id?: int|null, title: string, description?: string|null, clearCondition?: string|null, toolId?: int|null, sortOrder?: int|null, difficulty?: int|null, experiencePoints?: int|null, skillGrants?: list<string>, questTier?: string|null}>  $quests
     */
    private function syncChildQuests(QuestUnit $unit, array $quests): void
    {
        $keptIds = [];
        $index = 0;

        foreach ($quests as $questData) {
            $index++;
            $difficulty = \App\Support\QuestDifficulty::normalize($questData['difficulty'] ?? null);
            $toolIds = $this->resolveToolIds($questData);
            $attributes = [
                'title' => $questData['title'],
                'description' => $questData['description'] ?? '',
                'clear_condition' => $questData['clearCondition'] ?? '',
                'tool_id' => $toolIds[0] ?? null,
                'difficulty' => $difficulty,
                'experience_points' => \App\Support\QuestDifficulty::experiencePoints($difficulty),
                'sort_order' => $questData['sortOrder'] ?? $index,
                'is_published' => true,
            ];
            QuestTier::applyToAttributes($attributes, $questData['questTier'] ?? QuestTier::LOW);

            $existing = isset($questData['id'])
                ? $unit->quests()->whereKey($questData['id'])->first()
                : null;

            if ($existing !== null) {
                $existing->update($attributes);
                $this->questSkillGrantSync->syncForQuest($existing, $questData['skillGrants'] ?? []);
                $this->questToolSync->syncForQuest($existing, $toolIds);
                $keptIds[] = $existing->id;

                continue;
            }

            $created = $unit->quests()->create([
                ...$attributes,
                'type' => Quest::TYPE_PERSONAL,
                'is_required' => true,
                'reward_text' => null,
                'badge_label' => null,
                'brand_label' => null,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(2)->toDateString(),
            ]);
            $this->questSkillGrantSync->syncForQuest($created, $questData['skillGrants'] ?? []);
            $this->questToolSync->syncForQuest($created, $toolIds);
            $keptIds[] = $created->id;
        }

        $unit->quests()
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(fn (Quest $quest) => $quest->delete());
    }

    /**
     * @param  array{toolId?: int|null, toolIds?: list<int|null>}  $questData
     * @return list<int>
     */
    private function resolveToolIds(array $questData): array
    {
        $toolIds = $questData['toolIds'] ?? [];
        if ($toolIds !== []) {
            return array_values(array_filter(
                array_map(static fn ($id) => $id !== null ? (int) $id : null, $toolIds),
                static fn ($id) => $id !== null && $id > 0,
            ));
        }

        if (($questData['toolId'] ?? null) !== null) {
            return [(int) $questData['toolId']];
        }

        return [];
    }
}
