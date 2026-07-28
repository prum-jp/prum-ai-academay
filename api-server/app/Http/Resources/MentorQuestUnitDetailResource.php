<?php

namespace App\Http\Resources;

use App\Models\QuestUnit;
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
            'rewardText' => $this->reward_text ?? '',
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
                ])->values();
            }, []),
        ];
    }
}
