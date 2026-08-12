<?php

namespace App\Support;

use App\Models\QuestUnit;

class MentorQuestUnitFields
{
    /**
     * @return array<string, mixed>
     */
    public static function base(QuestUnit $unit): array
    {
        return [
            'id' => $unit->id,
            'title' => $unit->title,
            'description' => $unit->description ?? '',
            'sortOrder' => (int) $unit->sort_order,
            'rewardText' => '',
            'rewards' => $unit->relationLoaded('rewards')
                ? QuestRewardPresenter::statPoints($unit->rewards)
                : [],
        ];
    }
}
