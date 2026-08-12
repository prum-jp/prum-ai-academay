<?php

namespace App\Http\Resources;

use App\Models\QuestUnit;
use App\Support\MentorQuestUnitFields;
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
            ...MentorQuestUnitFields::base($this->resource),
            'questCount' => (int) ($this->quests_count ?? 0),
            'isPublished' => (bool) $this->is_published,
        ];
    }
}
