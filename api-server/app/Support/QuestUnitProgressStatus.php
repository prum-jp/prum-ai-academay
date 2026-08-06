<?php

namespace App\Support;

use Illuminate\Support\Collection;

final class QuestUnitProgressStatus
{
    public const NOT_STARTED = 'not_started';

    public const IN_PROGRESS = 'in_progress';

    public const HAS_REJECTED = 'has_rejected';

    public const COMPLETED = 'completed';

    /**
     * @var list<string>
     */
    public const ALL = [
        self::NOT_STARTED,
        self::IN_PROGRESS,
        self::HAS_REJECTED,
        self::COMPLETED,
    ];

    /**
     * @param  Collection<int, array<string, mixed>>|iterable<int, array<string, mixed>>  $quests
     */
    public static function resolveFromQuests(iterable $quests): string
    {
        $statuses = collect($quests)
            ->pluck('progressStatus')
            ->filter(fn ($status) => is_string($status) && $status !== '')
            ->values();

        if ($statuses->isEmpty()) {
            return self::NOT_STARTED;
        }

        if ($statuses->every(fn (string $status) => $status === QuestProgressStatus::COMPLETED)) {
            return self::COMPLETED;
        }

        if ($statuses->contains(QuestProgressStatus::REJECTED)) {
            return self::HAS_REJECTED;
        }

        if ($statuses->contains(QuestProgressStatus::IN_PROGRESS)) {
            return self::IN_PROGRESS;
        }

        if ($statuses->contains(QuestProgressStatus::REVIEW_REQUESTED)) {
            return self::IN_PROGRESS;
        }

        if ($statuses->contains(QuestProgressStatus::COMPLETED)) {
            return self::IN_PROGRESS;
        }

        return self::NOT_STARTED;
    }
}
