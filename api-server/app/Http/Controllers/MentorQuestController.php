<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMentorQuestRequest;
use App\Http\Requests\UpdateMentorPersonalQuestRequest;
use App\Http\Requests\UpdateMentorQuestRequest;
use App\Http\Resources\MentorQuestDetailResource;
use App\Http\Resources\MentorQuestResource;
use App\Models\Quest;
use App\Services\MentorQuestCatalogService;
use App\Services\MentorQuestRegistrar;
use App\Support\PaginationMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MentorQuestController extends Controller
{
    public function __construct(
        private readonly MentorQuestCatalogService $mentorQuestCatalogService,
        private readonly MentorQuestRegistrar $mentorQuestRegistrar,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in([Quest::TYPE_TEAM, Quest::TYPE_SPECIAL])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $paginator = $this->mentorQuestCatalogService->paginateBoardQuests(
            $validated['type'],
            $page,
        );

        $items = $paginator->getCollection()->map(
            fn (Quest $quest) => (new MentorQuestResource($quest))->resolve(),
        );

        return response()->json([
            'data' => $items,
            'meta' => PaginationMeta::fromPaginator($paginator),
        ]);
    }

    public function show(Quest $quest): JsonResponse
    {
        $quest->load(['rewards', 'tool', 'tools', 'questUnit']);

        return response()->json([
            'data' => (new MentorQuestDetailResource($quest))->resolve(),
        ]);
    }

    public function store(StoreMentorQuestRequest $request): JsonResponse
    {
        $quest = $this->mentorQuestRegistrar->register($request->validated());

        return response()->json([
            'data' => (new MentorQuestResource($quest))->resolve(),
        ], 201);
    }

    public function update(UpdateMentorQuestRequest $request, Quest $quest): JsonResponse
    {
        if ($quest->type === Quest::TYPE_PERSONAL) {
            abort(404);
        }

        $this->ensureBoardQuest($quest);

        $updated = $this->mentorQuestRegistrar->update($quest, $request->validated());

        return response()->json([
            'data' => (new MentorQuestResource($updated))->resolve(),
        ]);
    }

    public function updatePersonal(UpdateMentorPersonalQuestRequest $request, Quest $quest): JsonResponse
    {
        if ($quest->type !== Quest::TYPE_PERSONAL || $quest->quest_unit_id === null) {
            abort(404);
        }

        $updated = $this->mentorQuestRegistrar->updatePersonal($quest, $request->validated());

        return response()->json([
            'data' => (new MentorQuestDetailResource($updated))->resolve(),
        ]);
    }

    public function publish(Request $request, Quest $quest): JsonResponse
    {
        $this->ensureBoardQuest($quest);

        $validated = $request->validate([
            'isPublished' => ['required', 'boolean'],
        ]);

        $updated = $this->mentorQuestRegistrar->setPublished(
            $quest,
            (bool) $validated['isPublished'],
        );

        return response()->json([
            'data' => (new MentorQuestResource($updated))->resolve(),
        ]);
    }

    public function destroy(Quest $quest): JsonResponse
    {
        $this->ensureBoardQuest($quest);

        $this->mentorQuestRegistrar->delete($quest);

        return response()->json(status: 204);
    }

    private function ensureBoardQuest(Quest $quest): void
    {
        $isBoardQuest = $quest->quest_unit_id === null
            && in_array($quest->type, [Quest::TYPE_TEAM, Quest::TYPE_SPECIAL], true);

        if (! $isBoardQuest) {
            throw new NotFoundHttpException();
        }
    }
}
