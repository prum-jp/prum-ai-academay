<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectMentorStudentRequest;
use App\Http\Requests\StoreMentorStudentRequest;
use App\Http\Resources\MentorStudentResource;
use App\Models\User;
use App\Services\LevelCalculator;
use App\Services\MentorStudentRegistrar;
use App\Services\MentorStudentService;
use App\Support\AdventurerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorStudentController extends Controller
{
    public function __construct(
        private readonly LevelCalculator $levelCalculator,
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
                fn (User $student) => (new MentorStudentResource(
                    $student,
                    $this->levelCalculator,
                    $selectedId,
                ))->resolve(),
            )->values(),
            'meta' => [
                'selectedStudentId' => $selectedId,
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
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
            'data' => (new MentorStudentResource(
                $student,
                $this->levelCalculator,
                null,
            ))->resolve(),
        ], 201);
    }

    public function select(SelectMentorStudentRequest $request): JsonResponse
    {
        $student = $this->mentorStudentService->findStudent($request->validated('studentId'));

        AdventurerContext::setMentorTarget($request, $student);

        return response()->json([
            'data' => (new MentorStudentResource(
                $student,
                $this->levelCalculator,
                $student->id,
            ))->resolve(),
        ]);
    }
}
