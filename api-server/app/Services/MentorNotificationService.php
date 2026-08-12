<?php

namespace App\Services;

use App\Models\MentorNotification;
use App\Models\Quest;
use App\Models\User;
use App\Support\MentorNotificationType;

class MentorNotificationService
{
    public function notifyStudentComment(
        User $student,
        Quest $quest,
        string $body,
    ): void {
        if (! $student->isStudent()) {
            return;
        }

        $preview = mb_strlen($body) > 80 ? mb_substr($body, 0, 80).'…' : $body;
        $mentorIds = User::query()
            ->where('role', User::ROLE_MENTOR)
            ->pluck('id');

        foreach ($mentorIds as $mentorId) {
            $this->create((int) $mentorId, MentorNotificationType::COMMENT, [
                'studentId' => $student->id,
                'studentName' => $student->name,
                'questId' => $quest->id,
                'questTitle' => $quest->title,
                'commentPreview' => $preview,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function create(int $mentorId, string $type, array $metadata): MentorNotification
    {
        return MentorNotification::query()->create([
            'user_id' => $mentorId,
            'type' => $type,
            'metadata' => $metadata,
        ]);
    }
}
