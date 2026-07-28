<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestUnit;
use Illuminate\Support\Facades\DB;

class MentorQuestUnitRegistrar
{
    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     rewardText?: string|null,
     *     rewards?: list<array{stat: string, points: int}>
     * }  $payload
     */
    public function register(array $payload): QuestUnit
    {
        return DB::transaction(function () use ($payload): QuestUnit {
            $unit = QuestUnit::query()->create([
                'title' => $payload['title'],
                'description' => $payload['description'] ?? '',
                'reward_text' => $payload['rewardText'] ?? '',
                'sort_order' => ((int) QuestUnit::query()->max('sort_order')) + 1,
                'is_published' => false,
            ]);

            foreach ($payload['rewards'] ?? [] as $reward) {
                $unit->rewards()->create([
                    'stat' => $reward['stat'],
                    'points' => $reward['points'],
                ]);
            }

            return $unit->load('rewards')->loadCount('quests');
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     rewardText?: string|null,
     *     rewards?: list<array{stat: string, points: int}>,
     *     quests?: list<array{id?: int|null, title: string, description?: string|null, clearCondition?: string|null, toolId?: int|null, sortOrder?: int|null}>
     * }  $payload
     */
    public function update(QuestUnit $unit, array $payload): QuestUnit
    {
        return DB::transaction(function () use ($unit, $payload): QuestUnit {
            $unit->update([
                'title' => $payload['title'],
                'description' => $payload['description'] ?? '',
                'reward_text' => $payload['rewardText'] ?? '',
            ]);

            $unit->rewards()->delete();
            foreach ($payload['rewards'] ?? [] as $reward) {
                $unit->rewards()->create([
                    'stat' => $reward['stat'],
                    'points' => $reward['points'],
                ]);
            }

            $this->syncChildQuests($unit, $payload['quests'] ?? []);

            return $unit->fresh(['rewards'])->loadCount('quests');
        });
    }

    public function setPublished(QuestUnit $unit, bool $isPublished): QuestUnit
    {
        return DB::transaction(function () use ($unit, $isPublished): QuestUnit {
            $unit->update(['is_published' => $isPublished]);
            $this->syncChildQuestPublish($unit, $isPublished);

            return $unit->fresh(['rewards'])->loadCount('quests');
        });
    }

    public function syncChildQuestPublish(QuestUnit $unit, bool $isPublished): void
    {
        $unit->quests()->update(['is_published' => $isPublished]);
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
     * @param  list<array{id?: int|null, title: string, description?: string|null, clearCondition?: string|null, toolId?: int|null, sortOrder?: int|null, isPublished?: bool}>  $quests
     */
    private function syncChildQuests(QuestUnit $unit, array $quests): void
    {
        $keptIds = [];
        $index = 0;

        foreach ($quests as $questData) {
            $index++;
            $attributes = [
                'title' => $questData['title'],
                'description' => $questData['description'] ?? '',
                'clear_condition' => $questData['clearCondition'] ?? '',
                'tool_id' => $questData['toolId'] ?? null,
                'sort_order' => $questData['sortOrder'] ?? $index,
                'is_published' => $questData['isPublished'] ?? true,
            ];

            $existing = isset($questData['id'])
                ? $unit->quests()->whereKey($questData['id'])->first()
                : null;

            if ($existing !== null) {
                $existing->update($attributes);
                $keptIds[] = $existing->id;

                continue;
            }

            $created = $unit->quests()->create([
                ...$attributes,
                'type' => Quest::TYPE_PERSONAL,
                'is_required' => true,
                'unlock_level' => null,
                'reward_text' => null,
                'badge_label' => null,
                'brand_label' => null,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(2)->toDateString(),
            ]);
            $keptIds[] = $created->id;
        }

        $unit->quests()
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(fn (Quest $quest) => $quest->delete());
    }
}
