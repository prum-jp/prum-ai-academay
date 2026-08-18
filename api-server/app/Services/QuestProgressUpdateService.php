<?php

namespace App\Services;

use App\Http\Resources\QuestResource;
use App\Models\Quest;
use App\Models\StudentQuestProgress;
use App\Models\User;
use App\Support\QuestProgressStatus;
use App\Support\QuestSubmissionPresenter;
use App\Support\QuestSubmissionType;
use App\Support\StudentLevelResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestProgressUpdateService
{
    public function __construct(
        // TODO: 後に機能追加 — クエスト完了時の実績バッジ付与
        // private readonly BadgeAwarder $badgeAwarder,
        private readonly QuestBoardService $questBoardService,
        private readonly QuestActivityRecorder $questActivityRecorder,
        private readonly QuestSubmissionImageService $questSubmissionImageService,
        private readonly StudentExperienceService $studentExperienceService,
        private readonly StudentSkillGrantService $studentSkillGrantService,
        private readonly StudentLevelResolver $studentLevelResolver,
        private readonly StudentNotificationService $studentNotificationService,
        private readonly MentorNotificationService $mentorNotificationService,
        private readonly QuestSubmissionStorageService $questSubmissionStorageService,
    ) {}

    /**
     * @return array{quest: Quest, progress: StudentQuestProgress, studentLevel: int}
     */
    public function updateStatus(
        User $actor,
        User $student,
        Quest $quest,
        int $studentLevel,
        string $nextStatus,
        callable $canTransition,
        bool $requireExistingProgress = false,
    ): array {
        $this->assertQuestUnlocked($quest, $studentLevel);

        $progress = StudentQuestProgress::query()->firstOrNew([
            'user_id' => $student->id,
            'quest_id' => $quest->id,
        ]);

        if ($requireExistingProgress && ! $progress->exists) {
            throw ValidationException::withMessages([
                'quest' => ['このクエストの進捗が見つかりません。'],
            ]);
        }

        $previousStatus = QuestProgressStatus::resolveFromProgress(
            $progress->exists ? $progress : null,
        );
        $nextStatus = QuestProgressStatus::normalize($nextStatus);

        if (! $canTransition($previousStatus, $nextStatus)) {
            throw ValidationException::withMessages([
                'status' => ['このステータスには変更できません。'],
            ]);
        }

        if (
            $nextStatus === QuestProgressStatus::REVIEW_REQUESTED
            && $actor->isStudent()
            && ! QuestSubmissionPresenter::hasSubmission($progress->exists ? $progress : null)
        ) {
            throw ValidationException::withMessages([
                'status' => ['提出物を提出してからレビュー依頼してください。'],
            ]);
        }

        DB::transaction(function () use ($actor, $student, $quest, $progress, $previousStatus, $nextStatus): void {
            QuestProgressStatus::applyToProgress($progress, $nextStatus);
            $progress->save();

            $this->studentExperienceService->syncForStatusChange(
                $student,
                $quest,
                $previousStatus,
                $nextStatus,
            );

            $this->studentSkillGrantService->syncForStatusChange(
                $student,
                $quest,
                $previousStatus,
                $nextStatus,
            );

            $this->questActivityRecorder->recordStatusChange(
                $actor,
                $student,
                $quest->id,
                $previousStatus,
                $nextStatus,
            );
        });

        $this->studentNotificationService->notifyStatusChanged(
            $student,
            $quest,
            $previousStatus,
            $nextStatus,
            $actor,
        );

        if (
            $nextStatus === QuestProgressStatus::REVIEW_REQUESTED
            && $previousStatus !== QuestProgressStatus::REVIEW_REQUESTED
            && $actor->isStudent()
        ) {
            $this->mentorNotificationService->notifyReviewRequested($student, $quest);
        }

        $studentLevel = $this->studentLevelResolver->resolve($student->fresh(['studentStat']));

        return $this->buildResult($quest, $student, $progress, $studentLevel);
    }

    /**
     * @return array{quest: Quest, progress: StudentQuestProgress, studentLevel: int}
     */
    public function updateSubmission(
        User $actor,
        User $student,
        Quest $quest,
        int $studentLevel,
        string $type,
        ?string $url,
        ?string $text,
        ?UploadedFile $file,
    ): array {
        $this->assertQuestUnlocked($quest, $studentLevel);

        $type = in_array($type, QuestSubmissionType::ALL, true)
            ? $type
            : QuestSubmissionType::LINK;

        if ($type === QuestSubmissionType::IMAGE && $file !== null) {
            return $this->addSubmissionImage($actor, $student, $quest, $studentLevel, $file);
        }

        $progress = StudentQuestProgress::firstOrInitializeFor($student, $quest);

        DB::transaction(function () use ($actor, $student, $quest, $progress, $type, $url, $text, $file): void {
            if ($progress->submission_type === QuestSubmissionType::IMAGE && $type !== QuestSubmissionType::IMAGE) {
                $this->questSubmissionImageService->detachAll($progress);
            }

            [$storedReference, $storedText] = $this->resolveSubmissionPayload(
                $student,
                $quest,
                $type,
                $url,
                $text,
                $file,
            );

            $progress->submission_type = $type;
            $progress->submission_url = $storedReference !== '' ? $storedReference : null;
            $progress->submission_text = $storedText !== '' ? $storedText : null;
            $progress->save();

            $this->questActivityRecorder->recordSubmission(
                $actor,
                $student,
                $quest->id,
                $type,
                $progress->submission_url,
                $progress->submission_text,
            );
        });

        return $this->buildResult($quest, $student, $progress->fresh(['submissionFiles']), $studentLevel);
    }

    /**
     * @return array{quest: Quest, progress: StudentQuestProgress, studentLevel: int}
     */
    public function addSubmissionImage(
        User $actor,
        User $student,
        Quest $quest,
        int $studentLevel,
        UploadedFile $file,
    ): array {
        $this->assertQuestUnlocked($quest, $studentLevel);

        $progress = StudentQuestProgress::firstOrInitializeFor($student, $quest);
        $this->questSubmissionImageService->add($actor, $student, $quest, $progress, $file);

        return $this->buildResult($quest, $student, $progress->fresh(['submissionFiles']), $studentLevel);
    }

    /**
     * @return array{quest: Quest, progress: StudentQuestProgress, studentLevel: int}
     */
    public function deleteSubmissionImage(
        User $student,
        Quest $quest,
        int $studentLevel,
        int $fileId,
    ): array {
        $this->assertQuestUnlocked($quest, $studentLevel);

        $progress = $this->questSubmissionImageService->findProgressOrFail($student, $quest);
        $this->questSubmissionImageService->delete($progress, $fileId);

        return $this->buildResult($quest, $student, $progress->fresh(['submissionFiles']), $studentLevel);
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuestResourcePayload(array $result): array
    {
        return (new QuestResource($result['quest']))->additional([
            'studentLevel' => $result['studentLevel'],
        ])->resolve();
    }

    private function assertQuestUnlocked(Quest $quest, int $studentLevel): void
    {
        if ($quest->unlock_level !== null && $studentLevel < $quest->unlock_level) {
            throw ValidationException::withMessages([
                'quest' => ['このクエストはまだ解放されていません。'],
            ]);
        }
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveSubmissionPayload(
        User $student,
        Quest $quest,
        string $type,
        ?string $url,
        ?string $text,
        ?UploadedFile $file,
    ): array {
        if ($type === QuestSubmissionType::LINK) {
            return [trim((string) $url), null];
        }

        if ($type === QuestSubmissionType::TEXT) {
            return [null, trim((string) $text)];
        }

        if (QuestSubmissionType::isFileType($type) && $file !== null) {
            return [$this->questSubmissionStorageService->store($student, $quest, $file), null];
        }

        return [null, null];
    }

    /**
     * @return array{quest: Quest, progress: StudentQuestProgress, studentLevel: int}
     */
    private function buildResult(
        Quest $quest,
        User $student,
        StudentQuestProgress $progress,
        int $studentLevel,
    ): array {
        $this->questBoardService->loadQuestRelations($quest, $student);
        $progress->loadMissing('submissionFiles');
        $quest->setRelation('progressRecords', collect([$progress]));

        return [
            'quest' => $quest,
            'progress' => $progress,
            'studentLevel' => $studentLevel,
        ];
    }
}
