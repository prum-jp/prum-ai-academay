<?php

namespace App\Services;

use App\Models\StudentQuestComment;
use App\Models\User;
use App\Support\QuestActivityType;

class QuestActivityRecorder
{
    public function recordComment(
        User $actor,
        User $student,
        int $questId,
        string $body,
    ): StudentQuestComment {
        return $this->create(
            $actor,
            $student,
            $questId,
            QuestActivityType::COMMENT,
            [],
            trim($body),
        );
    }

    public function recordStatusChange(
        User $actor,
        User $student,
        int $questId,
        string $fromStatus,
        string $toStatus,
    ): StudentQuestComment {
        return $this->create(
            $actor,
            $student,
            $questId,
            QuestActivityType::STATUS_CHANGED,
            [
                'fromStatus' => $fromStatus,
                'toStatus' => $toStatus,
            ],
        );
    }

    public function recordSubmission(
        User $actor,
        User $student,
        int $questId,
        string $type,
        ?string $url,
        ?string $text,
    ): StudentQuestComment {
        return $this->create(
            $actor,
            $student,
            $questId,
            QuestActivityType::SUBMISSION_ADDED,
            array_filter([
                'type' => $type,
                'url' => $url,
                'text' => $text,
            ], fn ($value) => $value !== null && $value !== ''),
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function create(
        User $actor,
        User $student,
        int $questId,
        string $type,
        array $metadata,
        string $body = '',
    ): StudentQuestComment {
        return StudentQuestComment::query()->create([
            'student_user_id' => $student->id,
            'quest_id' => $questId,
            'author_id' => $actor->id,
            'type' => $type,
            'body' => $body,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }
}
