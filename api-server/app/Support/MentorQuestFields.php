<?php

namespace App\Support;

use App\Models\Quest;

class MentorQuestFields
{
    /**
     * @return array<string, mixed>
     */
    public static function base(Quest $quest): array
    {
        return [
            'id' => $quest->id,
            'title' => $quest->title,
            'description' => $quest->description ?? '',
            'clearCondition' => $quest->clear_condition ?? '',
            'type' => $quest->type,
            'isRequired' => (bool) $quest->is_required,
            'unlockLevel' => $quest->unlock_level,
            'rewardText' => $quest->reward_text ?? '',
            'badgeLabel' => $quest->badge_label,
            'difficulty' => $quest->difficulty,
            'experiencePoints' => (int) ($quest->experience_points ?? 0),
            'sortOrder' => (int) $quest->sort_order,
            'isPublished' => (bool) $quest->is_published,
        ];
    }
}
