<?php

namespace App\Http\Controllers;

use App\Services\QuestProgressUpdateService;
use App\Services\StudentQuestAccessService;
use App\Support\AdventurerContext;
use App\Support\QuestProgressStatus;
use App\Support\StudentLevelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MentorQuestProgressController extends Controller
{
    public function __construct(
        private readonly StudentLevelResolver $studentLevelResolver,
        private readonly StudentQuestAccessService $studentQuestAccessService,
        private readonly QuestProgressUpdateService $questProgressUpdateService,
    ) {}

    public function update(Request $request, int $questId): JsonResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(QuestProgressStatus::mentorSettableStatuses()),
            ],
        ]);

        /** @var \App\Models\User $actor */
        $actor = $request->user();
        $student = AdventurerContext::targetStudent($request);
        $studentLevel = $this->studentLevelResolver->resolve($student);
        $quest = $this->studentQuestAccessService->findQuestForStudent($student, $questId);

        $result = $this->questProgressUpdateService->updateStatus(
            $actor,
            $student,
            $quest,
            $studentLevel,
            $validated['status'],
            [QuestProgressStatus::class, 'mentorCanTransition'],
        );

        return response()->json(
            $this->questProgressUpdateService->toQuestResourcePayload($result),
        );
    }
}
