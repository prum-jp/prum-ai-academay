<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\StudentQuestProgress;
use App\Models\StudentQuestSubmissionFile;
use App\Models\User;
use App\Support\QuestSubmissionType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestSubmissionImageService
{
    public function __construct(
        private readonly QuestSubmissionStorageService $questSubmissionStorageService,
        private readonly QuestActivityRecorder $questActivityRecorder,
    ) {}

    public function add(
        User $actor,
        User $student,
        Quest $quest,
        StudentQuestProgress $progress,
        UploadedFile $file,
    ): void {
        $progress->ensureExists();

        DB::transaction(function () use ($actor, $student, $quest, $progress, $file): void {
            $this->assertCanAdd($progress);

            $path = $this->questSubmissionStorageService->store($student, $quest, $file);

            StudentQuestSubmissionFile::query()->create([
                'student_quest_progress_id' => $progress->id,
                'storage_path' => $path,
                'sort_order' => $this->nextSortOrder($progress),
            ]);

            $this->markProgressAsImageSubmission($progress);

            $this->questActivityRecorder->recordSubmission(
                $actor,
                $student,
                $quest->id,
                QuestSubmissionType::IMAGE,
                $path,
                null,
            );
        });
    }

    public function delete(StudentQuestProgress $progress, int $fileId): void
    {
        $submissionFile = $this->findForProgress($progress, $fileId);

        DB::transaction(function () use ($progress, $submissionFile): void {
            $this->questSubmissionStorageService->delete($submissionFile->storage_path);
            $submissionFile->delete();

            if (! $progress->submissionFiles()->exists()) {
                $progress->submission_type = null;
                $progress->save();
            }
        });
    }

    public function detachAll(StudentQuestProgress $progress): void
    {
        if (! $progress->exists) {
            return;
        }

        $progress->submissionFiles()->delete();
    }

    public function findProgressOrFail(User $student, Quest $quest): StudentQuestProgress
    {
        $progress = StudentQuestProgress::query()
            ->where('user_id', $student->id)
            ->where('quest_id', $quest->id)
            ->first();

        if ($progress === null) {
            throw ValidationException::withMessages([
                'file' => ['この画像は見つかりません。'],
            ]);
        }

        return $progress;
    }

    private function findForProgress(StudentQuestProgress $progress, int $fileId): StudentQuestSubmissionFile
    {
        $submissionFile = StudentQuestSubmissionFile::query()
            ->whereKey($fileId)
            ->where('student_quest_progress_id', $progress->id)
            ->first();

        if ($submissionFile === null) {
            throw ValidationException::withMessages([
                'file' => ['この画像は見つかりません。'],
            ]);
        }

        return $submissionFile;
    }

    private function assertCanAdd(StudentQuestProgress $progress): void
    {
        if ($progress->submissionFiles()->count() >= QuestSubmissionType::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'file' => ['画像は最大 '.QuestSubmissionType::MAX_IMAGES.' 枚まで登録できます。'],
            ]);
        }
    }

    private function nextSortOrder(StudentQuestProgress $progress): int
    {
        return ((int) $progress->submissionFiles()->max('sort_order')) + 1;
    }

    private function markProgressAsImageSubmission(StudentQuestProgress $progress): void
    {
        $progress->submission_type = QuestSubmissionType::IMAGE;
        $progress->submission_url = null;
        $progress->submission_text = null;
        $progress->save();
    }
}
