<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMentorCurriculumRequest;
use App\Http\Requests\UpdateMentorCurriculumRequest;
use App\Http\Resources\MentorCurriculumDetailResource;
use App\Http\Resources\MentorCurriculumResource;
use App\Models\Curriculum;
use App\Services\MentorCurriculumService;
use App\Services\MentorStudentAssignmentService;
use App\Support\PaginationMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorCurriculumController extends Controller
{
    public function __construct(
        private readonly MentorCurriculumService $curriculumService,
        private readonly MentorStudentAssignmentService $assignmentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $paginator = $this->curriculumService->paginate($page);

        return response()->json([
            'data' => $paginator->getCollection()->map(
                fn (Curriculum $curriculum) => (new MentorCurriculumResource($curriculum))->resolve(),
            )->values(),
            'meta' => PaginationMeta::fromPaginator($paginator),
        ]);
    }

    public function show(Curriculum $curriculum): JsonResponse
    {
        $curriculum = $this->curriculumService->find($curriculum->id);

        return response()->json([
            'data' => (new MentorCurriculumDetailResource($curriculum))->resolve(),
        ]);
    }

    public function store(StoreMentorCurriculumRequest $request): JsonResponse
    {
        $curriculum = $this->curriculumService->create($request->validated(), $request->user());

        return response()->json([
            'data' => (new MentorCurriculumResource($curriculum))->resolve(),
        ], 201);
    }

    public function update(UpdateMentorCurriculumRequest $request, Curriculum $curriculum): JsonResponse
    {
        $curriculum = $this->curriculumService->update($curriculum, $request->validated(), $request->user());

        return response()->json([
            'data' => (new MentorCurriculumResource($curriculum))->resolve(),
        ]);
    }

    public function destroy(Curriculum $curriculum): JsonResponse
    {
        $this->curriculumService->delete($curriculum);

        return response()->json(status: 204);
    }

    public function assignAllStudents(Request $request, Curriculum $curriculum): JsonResponse
    {
        $assignedCount = $this->assignmentService->assignCurriculumToAllStudents(
            $curriculum,
            $request->user(),
        );

        return response()->json([
            'data' => [
                'assignedCount' => $assignedCount,
            ],
        ]);
    }
}
