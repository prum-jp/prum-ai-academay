<?php

namespace App\Http\Resources;

use App\Models\Quest;
use App\Support\MentorQuestFields;
use App\Support\QuestRewardPresenter;
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
            ...MentorQuestFields::base($this->resource),
            'rewards' => $this->whenLoaded('rewards', function () {
                return QuestRewardPresenter::statPoints($this->rewards);
            }, []),
            'startsAt' => $this->starts_at?->toDateString(),
            'endsAt' => $this->ends_at?->toDateString(),
            'participantCount' => (int) ($this->applications_count ?? 0),
        ];
    }
}
