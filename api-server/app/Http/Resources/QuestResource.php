<?php

namespace App\Http\Resources;

use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Quest
 */
class QuestResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $studentLevel = (int) ($this->additional['studentLevel'] ?? 0);
        $unlockLevel = $this->unlock_level;
        $isLocked = $unlockLevel !== null && $studentLevel < $unlockLevel;

        $progress = $this->relationLoaded('progressRecords')
            ? $this->progressRecords->first()
            : null;

        $isChildQuest = $this->quest_unit_id !== null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'type' => $this->type,
            'questUnitId' => $this->quest_unit_id,
            'tool' => $this->when(
                $this->relationLoaded('tool'),
                fn () => $this->tool !== null
                    ? (new ToolResource($this->tool))->resolve()
                    : null,
                null,
            ),
            'isRequired' => (bool) $this->is_required,
            'unlockLevel' => $unlockLevel,
            'rewardText' => $isChildQuest ? '' : ($this->reward_text ?? ''),
            'rewards' => $isChildQuest ? [] : $this->whenLoaded('rewards', function () {
                return $this->rewards->map(fn ($reward) => [
                    'stat' => $reward->stat,
                    'points' => (int) $reward->points,
                ])->values();
            }, []),
            'badgeLabel' => $this->badge_label,
            'brandLabel' => $this->brand_label,
            'clearCondition' => $this->clear_condition ?? '',
            'sortOrder' => (int) $this->sort_order,
            'startsAt' => $this->starts_at?->toDateString(),
            'endsAt' => $this->ends_at?->toDateString(),
            'isLocked' => $isLocked,
            'isCompleted' => (bool) ($progress?->is_completed ?? false),
            'participantCount' => (int) ($this->applications_count ?? 0),
        ];
    }
}
