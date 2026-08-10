<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderMentorQuestUnitsRequest;
use App\Http\Requests\StoreMentorQuestUnitRequest;
use App\Http\Requests\UpdateMentorQuestUnitRequest;
use App\Http\Resources\MentorQuestUnitDetailResource;
use App\Http\Resources\MentorQuestUnitResource;
use App\Models\QuestUnit;
use App\Services\MentorQuestCatalogService;
use App\Services\MentorQuestUnitRegistrar;
use App\Support\PaginationMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorQuestUnitController extends Controller
{
    public function __construct(
        private readonly MentorQuestCatalogService $mentorQuestCatalogService,
        private readonly MentorQuestUnitRegistrar $mentorQuestUnitRegistrar,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $paginator = $this->mentorQuestCatalogService->paginateUnits($page);

        $items = $paginator->getCollection()->map(
            fn (QuestUnit $unit) => (new MentorQuestUnitResource($unit))->resolve(),
        );

        return response()->json([
            'data' => $items,
            'meta' => PaginationMeta::fromPaginator($paginator),
        ]);
    }

    public function show(QuestUnit $questUnit): JsonResponse
    {
        $questUnit->load([
            'rewards',
            'quests' => fn ($query) => $query->with(['rewards', 'tools'])->orderBy('sort_order')->orderBy('id'),
        ]);

        return response()->json([
            'data' => (new MentorQuestUnitDetailResource($questUnit))->resolve(),
        ]);
    }

    public function store(StoreMentorQuestUnitRequest $request): JsonResponse
    {
        $unit = $this->mentorQuestUnitRegistrar->register($request->validated());

        return response()->json([
            'data' => (new MentorQuestUnitResource($unit))->resolve(),
        ], 201);
    }

    public function reorder(ReorderMentorQuestUnitsRequest $request): JsonResponse
    {
        $this->mentorQuestUnitRegistrar->reorder($request->validated('unitIds'));

        return response()->json([
            'data' => [
                'success' => true,
            ],
        ]);
    }

    public function update(UpdateMentorQuestUnitRequest $request, QuestUnit $questUnit): JsonResponse
    {
        $unit = $this->mentorQuestUnitRegistrar->update($questUnit, $request->validated());

        return response()->json([
            'data' => (new MentorQuestUnitResource($unit))->resolve(),
        ]);
    }

    public function destroy(QuestUnit $questUnit): JsonResponse
    {
        $this->mentorQuestUnitRegistrar->delete($questUnit);

        return response()->json(status: 204);
    }
}
