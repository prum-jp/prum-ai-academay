<?php

namespace App\Support;

use App\Models\StudentQuestProgress;

final class QuestProgressStatus
{
    public const NOT_STARTED = 'not_started';

    public const IN_PROGRESS = 'in_progress';

    public const REVIEW_REQUESTED = 'review_requested';

    public const REJECTED = 'rejected';

    public const COMPLETED = 'completed';

    /**
     * @var list<string>
     */
    public const ALL = [
        self::NOT_STARTED,
        self::IN_PROGRESS,
        self::REVIEW_REQUESTED,
        self::REJECTED,
        self::COMPLETED,
    ];

    public static function normalize(mixed $value): string
    {
        if (is_string($value) && in_array($value, self::ALL, true)) {
            return $value;
        }

        return self::NOT_STARTED;
    }

    public static function applyToProgress(StudentQuestProgress $progress, string $status): void
    {
        $progress->status = self::normalize($status);
        $progress->is_completed = $progress->status === self::COMPLETED;
        $progress->completed_at = $progress->is_completed ? ($progress->completed_at ?? now()) : null;
    }

    public static function resolveFromProgress(?StudentQuestProgress $progress): string
    {
        if ($progress === null) {
            return self::NOT_STARTED;
        }

        if ($progress->status !== null && $progress->status !== '') {
            return self::normalize($progress->status);
        }

        return (bool) $progress->is_completed ? self::COMPLETED : self::NOT_STARTED;
    }

    public static function studentCanTransition(string $currentStatus, string $nextStatus): bool
    {
        if ($currentStatus === $nextStatus || $currentStatus === self::COMPLETED) {
            return false;
        }

        return match ($currentStatus) {
            self::NOT_STARTED => $nextStatus === self::IN_PROGRESS,
            self::IN_PROGRESS => in_array($nextStatus, [self::REVIEW_REQUESTED, self::NOT_STARTED], true),
            self::REVIEW_REQUESTED => $nextStatus === self::IN_PROGRESS,
            self::REJECTED => $nextStatus === self::IN_PROGRESS,
            default => false,
        };
    }

    public static function mentorCanTransition(string $currentStatus, string $nextStatus): bool
    {
        if ($currentStatus === $nextStatus || $currentStatus !== self::REVIEW_REQUESTED) {
            return false;
        }

        return in_array($nextStatus, [self::COMPLETED, self::REJECTED], true);
    }

    /**
     * @return list<string>
     */
    public static function mentorSettableStatuses(): array
    {
        return [
            self::COMPLETED,
            self::REJECTED,
        ];
    }
}
