<?php

namespace App\Services;

use App\Http\Resources\QuestResource;
use App\Models\Quest;
use App\Models\StudentQuestProgress;
use App\Models\User;
use App\Support\QuestProgressStatus;
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
        private readonly StudentExperienceService $studentExperienceService,
        private readonly StudentSkillGrantService $studentSkillGrantService,
        private readonly StudentLevelResolver $studentLevelResolver,
        private readonly StudentNotificationService $studentNotificationService,
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

        $progress = StudentQuestProgress::query()->firstOrNew([
            'user_id' => $student->id,
            'quest_id' => $quest->id,
        ]);

        if (! $progress->exists) {
            QuestProgressStatus::applyToProgress($progress, QuestProgressStatus::NOT_STARTED);
        }

        $previousUrl = $progress->submission_url;

        DB::transaction(function () use ($actor, $student, $quest, $progress, $type, $url, $text, $file, $previousUrl): void {
            $this->questSubmissionStorageService->deleteStoredUrl($previousUrl);

            $storedUrl = null;
            $storedText = null;

            if ($type === QuestSubmissionType::LINK) {
                $storedUrl = trim((string) $url);
                $storedText = null;
            } elseif ($type === QuestSubmissionType::TEXT) {
                $storedUrl = null;
                $storedText = trim((string) $text);
            } elseif (QuestSubmissionType::isFileType($type) && $file !== null) {
                $storedUrl = $this->questSubmissionStorageService->store($student, $quest, $file, $type);
                $storedText = null;
            }

            $progress->submission_type = $type;
            $progress->submission_url = $storedUrl !== '' ? $storedUrl : null;
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

        return $this->buildResult($quest, $student, $progress->fresh(), $studentLevel);
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
     * @return array{quest: Quest, progress: StudentQuestProgress, studentLevel: int}
     */
    private function buildResult(
        Quest $quest,
        User $student,
        StudentQuestProgress $progress,
        int $studentLevel,
    ): array {
        $this->questBoardService->loadQuestRelations($quest, $student);
        $quest->setRelation('progressRecords', collect([$progress]));

        return [
            'quest' => $quest,
            'progress' => $progress,
            'studentLevel' => $studentLevel,
        ];
    }
}
