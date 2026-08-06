<?php

namespace App\Support;

use App\Models\Quest;
use Illuminate\Support\Collection;

class QuestSkillGrantPresenter
{
    /**
     * @return list<string>
     */
    public static function fromQuest(Quest $quest): array
    {
        if (! $quest->relationLoaded('rewards')) {
            return [];
        }

        return self::fromRewards($quest->rewards);
    }

    /**
     * @param  Collection<int, \App\Models\QuestReward>|\Illuminate\Support\Collection<int, mixed>  $rewards
     * @return list<string>
     */
    public static function fromRewards(Collection $rewards): array
    {
        return SkillKeys::normalizeList(
            $rewards
                ->pluck('stat')
                ->map(fn ($stat) => (string) $stat)
                ->all(),
        );
    }
}
