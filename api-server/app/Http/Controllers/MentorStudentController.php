<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectMentorStudentRequest;
use App\Http\Requests\StoreMentorStudentRequest;
use App\Http\Resources\StudentListItemResource;
use App\Models\User;
use App\Services\MentorStudentRegistrar;
use App\Services\MentorStudentService;
use App\Support\AdventurerContext;
use App\Support\PaginationMeta;
use App\Support\StudentLevelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorStudentController extends Controller
{
    public function __construct(
        private readonly StudentLevelResolver $studentLevelResolver,
        private readonly MentorStudentService $mentorStudentService,
        private readonly MentorStudentRegistrar $mentorStudentRegistrar,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $target = AdventurerContext::resolveMentorTarget($request);
        $selectedId = $target?->id;
        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $this->mentorStudentService->paginateForMentor($search, $page);

        return response()->json([
            'data' => $paginator->getCollection()->map(
                fn (User $student) => (new StudentListItemResource(
                    $student,
                    $this->studentLevelResolver,
                    $selectedId,
                    includeEmail: true,
                ))->resolve(),
            )->values(),
            'meta' => array_merge(
                PaginationMeta::fromPaginator($paginator),
                ['selectedStudentId' => $selectedId],
            ),
        ]);
    }

    public function picker(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $paginator = $this->mentorStudentService->paginateForPicker($search, $page);

        return response()->json([
            'data' => $paginator->getCollection()->map(
                fn (User $student) => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ],
            )->values(),
            'meta' => PaginationMeta::fromPaginator($paginator),
        ]);
    }

    public function store(StoreMentorStudentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $student = $this->mentorStudentRegistrar->register([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => (int) $validated['role'],
        ]);

        return response()->json([
            'data' => (new StudentListItemResource(
                $student,
                $this->studentLevelResolver,
                null,
                includeEmail: true,
            ))->resolve(),
        ], 201);
    }

    public function select(SelectMentorStudentRequest $request): JsonResponse
    {
        $student = $this->mentorStudentService->findStudent($request->validated('studentId'));

        AdventurerContext::setMentorTarget($request, $student);

        return response()->json([
            'data' => (new StudentListItemResource(
                $student,
                $this->studentLevelResolver,
                $student->id,
                includeEmail: true,
            ))->resolve(),
        ]);
    }
}
