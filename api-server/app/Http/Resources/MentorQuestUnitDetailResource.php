<?php

namespace App\Http\Resources;

use App\Models\QuestUnit;
use App\Support\QuestSkillGrantPresenter;
use App\Support\QuestTier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin QuestUnit
 */
class MentorQuestUnitDetailResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'sortOrder' => (int) $this->sort_order,
            'rewardText' => '',
            'rewards' => $this->whenLoaded('rewards', function () {
                return $this->rewards->map(fn ($reward) => [
                    'stat' => $reward->stat,
                    'points' => (int) $reward->points,
                ])->values();
            }, []),
            'quests' => $this->whenLoaded('quests', function () {
                return $this->quests->map(fn ($quest) => [
                    'id' => $quest->id,
                    'title' => $quest->title,
                    'description' => $quest->description ?? '',
                    'clearCondition' => $quest->clear_condition ?? '',
                    'toolId' => $quest->tool_id,
                    'sortOrder' => (int) $quest->sort_order,
                    'isPublished' => (bool) $quest->is_published,
                    'difficulty' => $quest->difficulty,
                    'estimatedDuration' => $quest->estimated_duration,
                    'experiencePoints' => (int) ($quest->experience_points ?? 0),
                    'questTier' => QuestTier::resolve(
                        $quest->quest_tier,
                        $quest->unlock_level !== null ? (int) $quest->unlock_level : null,
                    ),
                    'skillGrants' => $quest->relationLoaded('rewards')
                        ? QuestSkillGrantPresenter::fromQuest($quest)
                        : [],
                ])->values();
            }, []),
        ];
    }
}
