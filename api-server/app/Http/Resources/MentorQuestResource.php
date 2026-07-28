<?php

namespace App\Http\Resources;

use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Quest
 */
class MentorQuestResource extends JsonResource
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
            'type' => $this->type,
            'isRequired' => (bool) $this->is_required,
            'unlockLevel' => $this->unlock_level,
            'rewardText' => $this->reward_text ?? '',
            'rewards' => $this->whenLoaded('rewards', function () {
                return $this->rewards->map(fn ($reward) => [
                    'stat' => $reward->stat,
                    'points' => (int) $reward->points,
                ])->values();
            }, []),
            'badgeLabel' => $this->badge_label,
            'clearCondition' => $this->clear_condition ?? '',
            'sortOrder' => (int) $this->sort_order,
            'startsAt' => $this->starts_at?->toDateString(),
            'endsAt' => $this->ends_at?->toDateString(),
            'participantCount' => (int) ($this->applications_count ?? 0),
            'isPublished' => (bool) $this->is_published,
        ];
    }
}
