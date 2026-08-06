<?php

namespace App\Services;

use App\Models\Curriculum;
use App\Models\Quest;
use App\Models\StudentNotification;
use App\Models\User;
use App\Support\QuestProgressStatus;
use App\Support\StudentNotificationType;

class StudentNotificationService
{
    public function notifyCurriculumAdded(
        User $student,
        Curriculum $curriculum,
        User $actor,
    ): void {
        if (! $student->isStudent() || $actor->id === $student->id) {
            return;
        }

        $this->create($student, StudentNotificationType::CURRICULUM_ADDED, [
            'curriculumId' => $curriculum->id,
            'curriculumName' => $curriculum->name,
            'actorId' => $actor->id,
            'actorName' => $actor->name,
        ]);
    }

    public function notifyStatusChanged(
        User $student,
        Quest $quest,
        string $fromStatus,
        string $toStatus,
        User $actor,
    ): void {
        if (! $student->isStudent() || $actor->id === $student->id) {
            return;
        }

        $this->create($student, StudentNotificationType::STATUS_CHANGED, [
            'questId' => $quest->id,
            'questTitle' => $quest->title,
            'fromStatus' => QuestProgressStatus::normalize($fromStatus),
            'toStatus' => QuestProgressStatus::normalize($toStatus),
            'actorId' => $actor->id,
            'actorName' => $actor->name,
        ]);
    }

    public function notifyComment(
        User $student,
        Quest $quest,
        User $actor,
        string $body,
    ): void {
        if (! $student->isStudent() || $actor->id === $student->id) {
            return;
        }

        $preview = mb_strlen($body) > 80 ? mb_substr($body, 0, 80).'…' : $body;

        $this->create($student, StudentNotificationType::COMMENT, [
            'questId' => $quest->id,
            'questTitle' => $quest->title,
            'actorId' => $actor->id,
            'actorName' => $actor->name,
            'commentPreview' => $preview,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function create(User $student, string $type, array $metadata): StudentNotification
    {
        return StudentNotification::query()->create([
            'user_id' => $student->id,
            'type' => $type,
            'metadata' => $metadata,
        ]);
    }
}
