<?php

namespace App\Services;

use App\Models\Quest;
use Illuminate\Support\Facades\DB;

class MentorQuestRegistrar
{
    /**
     * @param  array{
     *     type: string,
     *     title: string,
     *     description?: string|null,
     *     clearCondition?: string|null,
     *     isRequired?: bool,
     *     unlockLevel?: int|null,
     *     rewardText?: string|null,
     *     badgeLabel?: string|null,
     *     rewards?: list<array{stat: string, points: int}>
     * }  $payload
     */
    public function register(array $payload): Quest
    {
        return DB::transaction(function () use ($payload): Quest {
            $type = $payload['type'];

            $quest = Quest::query()->create([
                'title' => $payload['title'],
                'description' => $payload['description'] ?? '',
                'clear_condition' => $payload['clearCondition'] ?? '',
                'type' => $type,
                'quest_unit_id' => null,
                'tool_id' => null,
                'is_required' => $payload['isRequired'] ?? true,
                'unlock_level' => $payload['unlockLevel'] ?? null,
                'reward_text' => $payload['rewardText'] ?? '',
                'badge_label' => $payload['badgeLabel'] ?? null,
                'brand_label' => null,
                'sort_order' => ((int) Quest::query()
                    ->where('type', $type)
                    ->whereNull('quest_unit_id')
                    ->max('sort_order')) + 1,
                'is_published' => false,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(2)->toDateString(),
            ]);

            foreach ($payload['rewards'] ?? [] as $reward) {
                $quest->rewards()->create([
                    'stat' => $reward['stat'],
                    'points' => $reward['points'],
                ]);
            }

            return $quest->load('rewards');
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     clearCondition?: string|null,
     *     isRequired?: bool,
     *     unlockLevel?: int|null,
     *     rewardText?: string|null,
     *     badgeLabel?: string|null,
     *     rewards?: list<array{stat: string, points: int}>
     * }  $payload
     */
    public function update(Quest $quest, array $payload): Quest
    {
        return DB::transaction(function () use ($quest, $payload): Quest {
            $quest->update([
                'title' => $payload['title'],
                'description' => $payload['description'] ?? '',
                'clear_condition' => $payload['clearCondition'] ?? '',
                'is_required' => $payload['isRequired'] ?? true,
                'unlock_level' => $payload['unlockLevel'] ?? null,
                'reward_text' => $payload['rewardText'] ?? '',
                'badge_label' => $payload['badgeLabel'] ?? null,
            ]);

            $quest->rewards()->delete();
            foreach ($payload['rewards'] ?? [] as $reward) {
                $quest->rewards()->create([
                    'stat' => $reward['stat'],
                    'points' => $reward['points'],
                ]);
            }

            return $quest->fresh(['rewards']);
        });
    }

    public function setPublished(Quest $quest, bool $isPublished): Quest
    {
        $quest->update(['is_published' => $isPublished]);

        return $quest->fresh(['rewards']);
    }

    public function delete(Quest $quest): void
    {
        $quest->delete();
    }
}
