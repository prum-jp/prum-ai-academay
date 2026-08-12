<?php

namespace App\Http\Resources;

use App\Models\QuestUnit;
use App\Support\MentorChildQuestFields;
use App\Support\MentorQuestUnitFields;
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
            ...MentorQuestUnitFields::base($this->resource),
            'quests' => $this->whenLoaded('quests', function () {
                return $this->quests
                    ->map(fn ($quest) => MentorChildQuestFields::base($quest))
                    ->values();
            }, []),
        ];
    }
}
