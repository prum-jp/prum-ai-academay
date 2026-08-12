<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Support\QuestToolIdResolver;
use Illuminate\Support\Facades\DB;

class MentorQuestUnitRegistrar
{
    public function __construct(
        private readonly PersonalQuestWriter $personalQuestWriter,
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
     * @param  list<array{id?: int|null, title: string, description?: string|null, clearCondition?: string|null, toolId?: int|null, toolIds?: list<int|null>, sortOrder?: int|null, difficulty?: int|null, experiencePoints?: int|null, skillGrants?: list<string>, questTier?: string|null}>  $quests
     */
    private function syncChildQuests(QuestUnit $unit, array $quests): void
    {
        $keptIds = [];
        $index = 0;

        foreach ($quests as $questData) {
            $index++;
            $attributes = $this->personalQuestWriter->buildUnitChildAttributes($questData, $index);
            $toolIds = QuestToolIdResolver::resolve($questData);

            $existing = isset($questData['id'])
                ? $unit->quests()->whereKey($questData['id'])->first()
                : null;

            if ($existing !== null) {
                $existing->update($attributes);
                $this->personalQuestWriter->syncRelations($existing, $questData, $toolIds);
                $keptIds[] = $existing->id;

                continue;
            }

            $created = $unit->quests()->create([
                ...$attributes,
                ...$this->personalQuestWriter->newUnitChildDefaults(),
            ]);
            $this->personalQuestWriter->syncRelations($created, $questData, $toolIds);
            $keptIds[] = $created->id;
        }

        $unit->quests()
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(fn (Quest $quest) => $quest->delete());
    }
}
