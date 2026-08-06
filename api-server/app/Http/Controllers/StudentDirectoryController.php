<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdventurerProfileResource;
use App\Http\Resources\StudentListItemResource;
use App\Models\User;
use App\Services\LevelCalculator;
use App\Services\MentorStudentService;
use App\Services\StudentExperienceService;
use App\Support\PaginationMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDirectoryController extends Controller
{
    public function __construct(
        private readonly LevelCalculator $levelCalculator,
        private readonly MentorStudentService $mentorStudentService,
        private readonly StudentExperienceService $studentExperienceService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $paginator = $this->mentorStudentService->paginateForDirectory(
            $search,
            $page,
            $request->user()->id,
        );

        return response()->json([
            'data' => $paginator->getCollection()->map(
                fn (User $student) => (new StudentListItemResource(
                    $student,
                    $this->levelCalculator,
                    $this->studentExperienceService,
                ))->resolve(),
            )->values(),
            'meta' => PaginationMeta::fromPaginator($paginator),
        ]);
    }

    public function show(Request $request, int $studentId): JsonResponse
    {
        $student = $this->mentorStudentService->findStudent($studentId);
        $student->load(['studentProfile', 'studentStat']);

        $nextStudent = $this->mentorStudentService->findNextDirectoryStudent(
            $studentId,
            $request->user()->id,
        );

        return response()->json([
            ...(new AdventurerProfileResource(
                $student,
                $this->levelCalculator,
                $this->studentExperienceService,
            ))->resolve(),
            'navigation' => [
                'next' => $nextStudent === null
                    ? null
                    : [
                        'id' => $nextStudent->id,
                        'name' => $nextStudent->name,
                    ],
            ],
        ]);
    }
}
