<?php

namespace App\Http\Resources;

use App\Models\QuestUnit;
use App\Support\QuestRewardPresenter;
use App\Support\QuestUnitProgressStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin QuestUnit
 */
class QuestUnitResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $studentLevel = (int) ($this->additional['studentLevel'] ?? 0);
        $quests = $this->whenLoaded('quests', function () use ($studentLevel) {
            return $this->quests->map(
                fn ($quest) => (new QuestResource($quest))->additional([
                    'studentLevel' => $studentLevel,
                ])->resolve(),
            )->values();
        }, []);

        $questItems = collect($quests);
        $completedCount = $questItems->where('isCompleted', true)->count();
        $totalCount = $questItems->count();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => '',
            'sortOrder' => (int) $this->sort_order,
            'rewardText' => '',
            'rewards' => $this->whenLoaded('rewards', function () {
                return QuestRewardPresenter::statPoints($this->rewards);
            }, []),
            'quests' => $quests,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'isCompleted' => $totalCount > 0 && $completedCount === $totalCount,
            'progressStatus' => QuestUnitProgressStatus::resolveFromQuests($questItems),
        ];
    }
}
