<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMentorStudentAssignmentsRequest;
use App\Services\MentorStudentAssignmentService;
use App\Services\MentorStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorStudentAssignmentController extends Controller
{
    public function __construct(
        private readonly MentorStudentService $mentorStudentService,
        private readonly MentorStudentAssignmentService $assignmentService,
    ) {}

    public function show(int $studentId): JsonResponse
    {
        $student = $this->mentorStudentService->findStudent($studentId);
        $options = $this->assignmentService->assignmentOptionsForStudent($student);

        return response()->json([
            'data' => [
                'studentId' => $student->id,
                'studentName' => $student->name,
                ...$options,
            ],
        ]);
    }

    public function update(UpdateMentorStudentAssignmentsRequest $request, int $studentId): JsonResponse
    {
        $student = $this->mentorStudentService->findStudent($studentId);
        $validated = $request->validated();

        $this->assignmentService->syncAssignments(
            $student,
            array_map('intval', $validated['curriculumIds']),
            array_map('intval', $validated['unitIds']),
            $request->user(),
        );

        $options = $this->assignmentService->assignmentOptionsForStudent($student);

        return response()->json([
            'data' => [
                'studentId' => $student->id,
                'studentName' => $student->name,
                ...$options,
            ],
        ]);
    }
}
