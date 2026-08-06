<?php

namespace App\Http\Resources;

use App\Models\Quest;
use App\Support\QuestSkillGrantPresenter;
use App\Support\QuestTier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Quest
 */
class MentorQuestDetailResource extends JsonResource
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
            'clearCondition' => $this->clear_condition ?? '',
            'type' => $this->type,
            'sortOrder' => (int) $this->sort_order,
            'toolId' => $this->tool_id,
            'tool' => $this->whenLoaded('tool', fn () => $this->tool === null ? null : [
                'id' => $this->tool->id,
                'code' => $this->tool->code,
                'name' => $this->tool->name,
            ]),
            'estimatedDuration' => $this->estimated_duration,
            'difficulty' => $this->difficulty,
            'experiencePoints' => (int) ($this->experience_points ?? 0),
            'unitId' => $this->quest_unit_id,
            'unitTitle' => $this->whenLoaded('questUnit', fn () => $this->questUnit?->title),
            'isRequired' => (bool) $this->is_required,
            'unlockLevel' => $this->unlock_level,
            'questTier' => QuestTier::resolve($this->quest_tier, $this->unlock_level !== null ? (int) $this->unlock_level : null),
            'rewardText' => $this->reward_text ?? '',
            'badgeLabel' => $this->badge_label,
            'skillGrants' => QuestSkillGrantPresenter::fromQuest($this->resource),
            'rewards' => $this->whenLoaded('rewards', function () {
                return $this->rewards->map(fn ($reward) => [
                    'skill' => $reward->stat,
                    'points' => 1,
                ])->values();
            }, []),
            'isPublished' => (bool) $this->is_published,
        ];
    }
}
