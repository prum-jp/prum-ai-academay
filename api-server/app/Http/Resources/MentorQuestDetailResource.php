<?php

namespace App\Http\Resources;

use App\Models\Quest;
use App\Support\MentorChildQuestFields;
use App\Support\QuestRewardPresenter;
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
            ...MentorChildQuestFields::base($this->resource),
            'type' => $this->type,
            'unitId' => $this->quest_unit_id,
            'unitTitle' => $this->whenLoaded('questUnit', fn () => $this->questUnit?->title),
            'isRequired' => (bool) $this->is_required,
            'unlockLevel' => $this->unlock_level,
            'rewardText' => $this->reward_text ?? '',
            'badgeLabel' => $this->badge_label,
            'rewards' => $this->whenLoaded('rewards', function () {
                return QuestRewardPresenter::skillPoints($this->rewards);
            }, []),
            'tool' => $this->whenLoaded('tool', fn () => $this->tool === null ? null : [
                'id' => $this->tool->id,
                'name' => $this->tool->name,
            ]),
            'tools' => $this->whenLoaded('tools', fn () => $this->tools
                ->map(fn ($tool) => (new ToolResource($tool))->resolve())
                ->values()
                ->all(), []),
        ];
    }
}
