<?php

namespace App\Http\Resources;

use App\Models\Curriculum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Curriculum
 */
class MentorCurriculumResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sortOrder' => $this->sort_order,
            'unitCount' => $this->quest_units_count ?? $this->questUnits()->count(),
        ];
    }
}
