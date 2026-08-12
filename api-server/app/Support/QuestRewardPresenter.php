<?php

namespace App\Support;

use Illuminate\Support\Collection;

class QuestRewardPresenter
{
    /**
     * @param  Collection<int, \App\Models\QuestReward>  $rewards
     * @return list<array{stat: string, points: int}>
     */
    public static function statPoints(Collection $rewards): array
    {
        return $rewards
            ->map(fn ($reward) => [
                'stat' => $reward->stat,
                'points' => (int) $reward->points,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, \App\Models\QuestReward>  $rewards
     * @return list<array{skill: string, points: int}>
     */
    public static function skillPoints(Collection $rewards): array
    {
        return $rewards
            ->map(fn ($reward) => [
                'skill' => $reward->stat,
                'points' => 1,
            ])
            ->values()
            ->all();
    }
}
