<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestResource;
use App\Models\Quest;
use App\Models\StudentQuestProgress;
use App\Services\BadgeAwarder;
use App\Services\QuestBoardService;
use App\Support\AdventurerContext;
use App\Support\PaginationMeta;
use App\Support\StudentLevelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestController extends Controller
{
    public function __construct(
        private readonly StudentLevelResolver $studentLevelResolver,
        private readonly QuestBoardService $questBoardService,
        private readonly BadgeAwarder $badgeAwarder,
    ) {}

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

    public function toggleProgress(Request $request, int $id): JsonResponse
    {
        $student = AdventurerContext::targetStudent($request);
        $quest = Quest::query()->findOrFail($id);
        $studentLevel = $this->studentLevelResolver->resolve($student);

        if ($quest->unlock_level !== null && $studentLevel < $quest->unlock_level) {
            throw ValidationException::withMessages([
                'quest' => ['このクエストはまだ解放されていません。'],
            ]);
        }

        $progress = StudentQuestProgress::query()->firstOrNew([
            'user_id' => $student->id,
            'quest_id' => $quest->id,
        ]);

        $nextCompleted = ! (bool) $progress->is_completed;
        $progress->is_completed = $nextCompleted;
        $progress->completed_at = $nextCompleted ? now() : null;
        $progress->save();

        if ($nextCompleted) {
            $this->badgeAwarder->awardForQuestCompletion($student, $quest);
        }

        $this->questBoardService->loadQuestRelations($quest, $student);
        $quest->setRelation('progressRecords', collect([$progress]));

        return response()->json(
            (new QuestResource($quest))->additional([
                'studentLevel' => $studentLevel,
            ])->resolve(),
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
