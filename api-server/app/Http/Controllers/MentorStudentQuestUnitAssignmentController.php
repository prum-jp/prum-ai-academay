<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Services\MentorStudentAssignmentService;
use App\Services\MentorStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorStudentQuestUnitAssignmentController extends Controller
{
    public function __construct(
        private readonly MentorStudentService $mentorStudentService,
        private readonly MentorStudentAssignmentService $assignmentService,
    ) {}

    public function index(int $studentId): JsonResponse
    {
        $student = $this->mentorStudentService->findStudent($studentId);

        return response()->json([
            'data' => $this->assignmentService->questUnitAssignmentStatusForStudent($student),
        ]);
    }

    public function store(Request $request, int $studentId, QuestUnit $questUnit): JsonResponse
    {
        $student = $this->mentorStudentService->findStudent($studentId);
        $this->assignmentService->assignUnitToStudent($student, $questUnit, $request->user());

        return response()->json([
            'data' => $this->assignmentService->questUnitAssignmentStatusForStudent($student),
        ]);
    }

    public function destroy(int $studentId, QuestUnit $questUnit): JsonResponse
    {
        $student = $this->mentorStudentService->findStudent($studentId);
        $removed = $this->assignmentService->unassignUnitFromStudent($student, $questUnit);

        if (! $removed) {
            return response()->json([
                'message' => 'カリキュラム経由で反映されているため、ここから解除できません。',
            ], 422);
        }

        return response()->json([
            'data' => $this->assignmentService->questUnitAssignmentStatusForStudent($student),
        ]);
    }

    public function storeQuest(Request $request, int $studentId, Quest $quest): JsonResponse
    {
        if ($quest->type !== Quest::TYPE_PERSONAL || $quest->quest_unit_id === null) {
            abort(404);
        }

        $student = $this->mentorStudentService->findStudent($studentId);
        $this->assignmentService->assignQuestToStudent($student, $quest, $request->user());

        return response()->json([
            'data' => $this->assignmentService->questUnitAssignmentStatusForStudent($student),
        ]);
    }

    public function destroyQuest(int $studentId, Quest $quest): JsonResponse
    {
        if ($quest->type !== Quest::TYPE_PERSONAL || $quest->quest_unit_id === null) {
            abort(404);
        }

        $student = $this->mentorStudentService->findStudent($studentId);
        $removed = $this->assignmentService->unassignQuestFromStudent($student, $quest);

        if (! $removed) {
            return response()->json([
                'message' => 'カリキュラム経由で反映されているため、ここから解除できません。',
            ], 422);
        }

        return response()->json([
            'data' => $this->assignmentService->questUnitAssignmentStatusForStudent($student),
        ]);
    }
}
