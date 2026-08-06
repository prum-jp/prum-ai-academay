<?php

namespace App\Http\Controllers;

use App\Models\QuestUnit;
use App\Services\MentorStudentAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorQuestUnitAssignmentController extends Controller
{
    public function __construct(
        private readonly MentorStudentAssignmentService $assignmentService,
    ) {}

    public function assignAllStudents(Request $request, QuestUnit $questUnit): JsonResponse
    {
        $assignedCount = $this->assignmentService->assignUnitToAllStudents(
            $questUnit,
            $request->user(),
        );

        return response()->json([
            'data' => [
                'assignedCount' => $assignedCount,
            ],
        ]);
    }
}
