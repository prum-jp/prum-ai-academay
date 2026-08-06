<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestCommentResource;
use App\Models\Quest;
use App\Models\StudentQuestComment;
use App\Models\User;
use App\Services\QuestActivityRecorder;
use App\Services\StudentNotificationService;
use App\Services\StudentQuestAccessService;
use App\Support\AdventurerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestCommentController extends Controller
{
    public function __construct(
        private readonly StudentQuestAccessService $studentQuestAccessService,
        private readonly QuestActivityRecorder $questActivityRecorder,
        private readonly StudentNotificationService $studentNotificationService,
    ) {}

    public function index(Request $request, int $questId): JsonResponse
    {
        $student = AdventurerContext::targetStudent($request);
        $this->studentQuestAccessService->findQuestForStudent($student, $questId);

        $comments = StudentQuestComment::query()
            ->where('student_user_id', $student->id)
            ->where('quest_id', $questId)
            ->with(['author.studentProfile'])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => $comments
                ->map(fn (StudentQuestComment $comment) => (new QuestCommentResource($comment))->resolve())
                ->values(),
        ]);
    }

    public function store(Request $request, int $questId): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        /** @var User $author */
        $author = $request->user();
        $student = AdventurerContext::targetStudent($request);
        $this->studentQuestAccessService->findQuestForStudent($student, $questId);

        $comment = $this->questActivityRecorder->recordComment(
            $author,
            $student,
            $questId,
            $validated['body'],
        );

        $quest = Quest::query()->findOrFail($questId);
        $this->studentNotificationService->notifyComment(
            $student,
            $quest,
            $author,
            $validated['body'],
        );

        $comment->load(['author.studentProfile']);

        return response()->json(
            (new QuestCommentResource($comment))->resolve(),
            201,
        );
    }
}
