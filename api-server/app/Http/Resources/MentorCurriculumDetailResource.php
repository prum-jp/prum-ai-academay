<?php

namespace App\Http\Resources;

use App\Models\Curriculum;
use App\Services\MentorStudentAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Curriculum
 */
class MentorCurriculumDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var MentorStudentAssignmentService $assignmentService */
        $assignmentService = app(MentorStudentAssignmentService::class);
        $assignmentState = $assignmentService->curriculumAssignmentState($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sortOrder' => $this->sort_order,
            'unitIds' => $this->questUnits->pluck('id')->values()->all(),
            'units' => $this->questUnits->map(fn ($unit) => [
                'id' => $unit->id,
                'title' => $unit->title,
            ])->values()->all(),
            'assignmentTarget' => $assignmentState['assignmentTarget'],
            'assignedStudentIds' => $assignmentState['assignedStudentIds'],
        ];
    }
}
