<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestResource;
use App\Http\Resources\QuestUnitResource;
use App\Models\QuestUnit;
use App\Services\QuestUnitService;
use App\Services\StudentQuestAccessService;
use App\Support\AdventurerContext;
use App\Support\PaginationMeta;
use App\Support\StudentLevelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestUnitController extends Controller
{
    public function __construct(
        private readonly StudentLevelResolver $studentLevelResolver,
        private readonly QuestUnitService $questUnitService,
        private readonly StudentQuestAccessService $studentQuestAccessService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'progressFilter' => ['sometimes', 'string', Rule::in(QuestUnitService::PROGRESS_FILTERS)],
        ]);

        $student = AdventurerContext::targetStudent($request);
        $studentLevel = $this->studentLevelResolver->resolve($student);
        $page = max(1, (int) ($validated['page'] ?? 1));
        $progressFilter = $validated['progressFilter'] ?? 'all';

        $paginator = $this->questUnitService->paginateForStudent($student, $page, $progressFilter);

        $items = $paginator->getCollection()->map(
            fn (QuestUnit $unit) => (new QuestUnitResource($unit))->additional([
                'studentLevel' => $studentLevel,
            ])->resolve(),
        );

        return response()->json([
            'data' => $items,
            'meta' => PaginationMeta::fromPaginator($paginator),
            'studentLevel' => $studentLevel,
        ]);
    }

    public function show(Request $request, int $questUnitId): JsonResponse
    {
        $student = AdventurerContext::targetStudent($request);
        $studentLevel = $this->studentLevelResolver->resolve($student);
        $unit = $this->studentQuestAccessService->findUnitForStudent($student, $questUnitId);

        return response()->json([
            'data' => (new QuestUnitResource($unit))->additional([
                'studentLevel' => $studentLevel,
            ])->resolve(),
            'studentLevel' => $studentLevel,
        ]);
    }
}
