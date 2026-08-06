<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateQuestSubmissionRequest;
use App\Http\Resources\QuestResource;
use App\Models\Quest;
use App\Services\QuestBoardService;
use App\Services\QuestProgressUpdateService;
use App\Services\StudentQuestAccessService;
use App\Support\AdventurerContext;
use App\Support\PaginationMeta;
use App\Support\QuestProgressStatus;
use App\Support\StudentLevelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestController extends Controller
{
    public function __construct(
        private readonly StudentLevelResolver $studentLevelResolver,
        private readonly QuestBoardService $questBoardService,
        private readonly StudentQuestAccessService $studentQuestAccessService,
        private readonly QuestProgressUpdateService $questProgressUpdateService,
    ) {}

    public function show(Request $request, int $id): JsonResponse
    {
        $student = AdventurerContext::targetStudent($request);
        $studentLevel = $this->studentLevelResolver->resolve($student);
        $quest = $this->studentQuestAccessService->findQuestForStudent($student, $id);

        return response()->json([
            'data' => (new QuestResource($quest))->additional([
                'studentLevel' => $studentLevel,
            ])->resolve(),
            'studentLevel' => $studentLevel,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(Quest::TYPES)],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $student = AdventurerContext::targetStudent($request);
        $studentLevel = $this->studentLevelResolver->resolve($student);
        $page = max(1, (int) ($validated['page'] ?? 1));

        $paginator = $this->questBoardService->paginateQuests(
            $student,
            $validated['type'],
            $page,
        );

        return $this->paginatedQuestResponse($paginator, $studentLevel);
    }

    public function updateProgress(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(QuestProgressStatus::ALL)],
        ]);

        /** @var \App\Models\User $actor */
        $actor = $request->user();
        $student = AdventurerContext::targetStudent($request);
        $studentLevel = $this->studentLevelResolver->resolve($student);
        $quest = $this->studentQuestAccessService->findQuestForStudent($student, $id);

        $result = $this->questProgressUpdateService->updateStatus(
            $actor,
            $student,
            $quest,
            $studentLevel,
            $validated['status'],
            [QuestProgressStatus::class, 'studentCanTransition'],
        );

        return response()->json(
            $this->questProgressUpdateService->toQuestResourcePayload($result),
        );
    }

    public function updateSubmission(UpdateQuestSubmissionRequest $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $actor */
        $actor = $request->user();
        $student = AdventurerContext::targetStudent($request);
        $studentLevel = $this->studentLevelResolver->resolve($student);
        $quest = $this->studentQuestAccessService->findQuestForStudent($student, $id);
        $validated = $request->validated();

        $result = $this->questProgressUpdateService->updateSubmission(
            $actor,
            $student,
            $quest,
            $studentLevel,
            (string) $validated['type'],
            isset($validated['url']) ? (string) $validated['url'] : null,
            isset($validated['text']) ? (string) $validated['text'] : null,
            $request->file('file'),
        );

        return response()->json(
            $this->questProgressUpdateService->toQuestResourcePayload($result),
        );
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Quest>  $paginator
     */
    private function paginatedQuestResponse($paginator, int $studentLevel): JsonResponse
    {
        $items = $paginator->getCollection()->map(
            fn (Quest $quest) => (new QuestResource($quest))->additional([
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
