<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestUnitResource;
use App\Models\QuestUnit;
use App\Services\QuestUnitService;
use App\Support\AdventurerContext;
use App\Support\PaginationMeta;
use App\Support\StudentLevelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestUnitController extends Controller
{
    public function __construct(
        private readonly StudentLevelResolver $studentLevelResolver,
        private readonly QuestUnitService $questUnitService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $student = AdventurerContext::targetStudent($request);
        $studentLevel = $this->studentLevelResolver->resolve($student);
        $page = max(1, (int) ($validated['page'] ?? 1));

        $paginator = $this->questUnitService->paginateForStudent($student, $page);

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
}
