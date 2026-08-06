<?php

namespace App\Http\Resources;

use App\Models\Quest;
use App\Support\QuestProgressStatus;
use App\Support\QuestSkillGrantPresenter;
use App\Support\QuestSubmissionPresenter;
use App\Support\QuestTier;
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
            'questTier' => QuestTier::resolve($this->quest_tier, $unlockLevel !== null ? (int) $unlockLevel : null),
            'rewardText' => $isChildQuest ? '' : ($this->reward_text ?? ''),
            'skillGrants' => QuestSkillGrantPresenter::fromQuest($this->resource),
            'rewards' => $this->whenLoaded('rewards', function () {
                return $this->rewards->map(fn ($reward) => [
                    'skill' => $reward->stat,
                    'points' => 1,
                ])->values();
            }, []),
            'badgeLabel' => $this->badge_label,
            'brandLabel' => $this->brand_label,
            'clearCondition' => $this->clear_condition ?? '',
            'estimatedDuration' => $this->estimated_duration,
            'difficulty' => $this->difficulty,
            'experiencePoints' => (int) ($this->experience_points ?? 0),
            'sortOrder' => (int) $this->sort_order,
            'unitTitle' => $this->when(
                $this->relationLoaded('questUnit') && $this->questUnit !== null,
                fn () => $this->questUnit->title,
                null,
            ),
            'unitSortOrder' => $this->when(
                $this->relationLoaded('questUnit') && $this->questUnit !== null,
                fn () => (int) $this->questUnit->sort_order,
                null,
            ),
            'startsAt' => $this->starts_at?->toDateString(),
            'endsAt' => $this->ends_at?->toDateString(),
            'isLocked' => $isLocked,
            'isCompleted' => (bool) ($progress?->is_completed ?? false),
            'progressStatus' => QuestProgressStatus::resolveFromProgress($progress),
            'submission' => QuestSubmissionPresenter::fromProgress($progress),
            'submissionUrl' => $progress?->submission_url,
            'participantCount' => (int) ($this->applications_count ?? 0),
        ];
    }
}
