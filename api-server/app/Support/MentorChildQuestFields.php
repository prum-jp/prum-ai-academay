<?php

namespace App\Support;

use App\Models\Quest;

class MentorChildQuestFields
{
    /**
     * Shared child/personal quest shape for unit detail lists and quest detail API.
     *
     * @return array<string, mixed>
     */
    public static function base(Quest $quest): array
    {
        return [
            'id' => $quest->id,
            'title' => $quest->title,
            'description' => $quest->description ?? '',
            'clearCondition' => $quest->clear_condition ?? '',
            'toolId' => $quest->tool_id,
            'toolIds' => $quest->relationLoaded('tools')
                ? $quest->tools->pluck('id')->values()->all()
                : [],
            'sortOrder' => (int) $quest->sort_order,
            'isPublished' => (bool) $quest->is_published,
            'difficulty' => $quest->difficulty,
            'estimatedDuration' => $quest->estimated_duration,
            'experiencePoints' => (int) ($quest->experience_points ?? 0),
            'questTier' => QuestTier::resolve(
                $quest->quest_tier,
                $quest->unlock_level !== null ? (int) $quest->unlock_level : null,
            ),
            'skillGrants' => QuestSkillGrantPresenter::fromQuest($quest),
        ];
    }
}
