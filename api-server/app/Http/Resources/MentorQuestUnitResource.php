<?php

namespace App\Http\Resources;

use App\Models\QuestUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin QuestUnit
 */
class MentorQuestUnitResource extends JsonResource
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
            'rewardText' => $this->reward_text ?? '',
            'rewards' => $this->whenLoaded('rewards', function () {
                return $this->rewards->map(fn ($reward) => [
                    'stat' => $reward->stat,
                    'points' => (int) $reward->points,
                ])->values();
            }, []),
            'questCount' => (int) ($this->quests_count ?? 0),
            'isPublished' => (bool) $this->is_published,
        ];
    }
}
