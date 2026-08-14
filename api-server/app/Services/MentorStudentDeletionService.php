<?php

namespace App\Services;

use App\Models\StudentQuestProgress;
use App\Models\User;
use App\Support\AdventurerContext;
use App\Support\PublicStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MentorStudentDeletionService
{
    public function __construct(
        private readonly StudentAvatarService $avatarService,
    ) {}

    public function delete(User $student, ?Request $request = null): void
    {
        if (! $student->isStudent()) {
            throw ValidationException::withMessages([
                'student' => ['受講生のみ削除できます。'],
            ]);
        }

        DB::transaction(function () use ($student, $request): void {
            $this->purgeStoredFiles($student);

            $student->delete();

            if (
                $request !== null
                && (int) $request->session()->get(AdventurerContext::SESSION_TARGET_STUDENT_ID) === (int) $student->id
            ) {
                AdventurerContext::clearMentorTarget($request);
            }
        });
    }

    private function purgeStoredFiles(User $student): void
    {
        $student->loadMissing('studentProfile');

        $this->avatarService->delete($student);

        StudentQuestProgress::query()
            ->where('user_id', $student->id)
            ->pluck('submission_url')
            ->each(fn (?string $reference) => PublicStorage::deleteStoredReference($reference));

        $disk = PublicStorage::disk();
        $disk->deleteDirectory('avatars/'.$student->id);
        $disk->deleteDirectory('quest-submissions/'.$student->id);
    }
}
