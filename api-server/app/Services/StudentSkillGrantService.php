<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\StudentStat;
use App\Models\User;
use App\Support\QuestProgressStatus;
use App\Support\SkillKeys;

class StudentSkillGrantService
{
    public function syncForStatusChange(
        User $student,
        Quest $quest,
        string $previousStatus,
        string $nextStatus,
    ): void {
        $wasCompleted = $previousStatus === QuestProgressStatus::COMPLETED;
        $isCompleted = $nextStatus === QuestProgressStatus::COMPLETED;

        if (! $wasCompleted && $isCompleted) {
            $this->award($student, $quest);

            return;
        }

        if ($wasCompleted && ! $isCompleted) {
            $this->revoke($student, $quest);
        }
    }

    private function award(User $student, Quest $quest): void
    {
        $quest->loadMissing('rewards');
        if ($quest->rewards->isEmpty()) {
            return;
        }

        $stat = StudentStat::ensureForUser($student);

        foreach ($quest->rewards as $reward) {
            $column = SkillKeys::COLUMN_MAP[$reward->stat] ?? null;
            if ($column === null) {
                continue;
            }

            $stat->{$column} = (int) $stat->{$column} + 1;
        }

        $stat->save();
    }

    private function revoke(User $student, Quest $quest): void
    {
        $quest->loadMissing('rewards');
        if ($quest->rewards->isEmpty()) {
            return;
        }

        $stat = StudentStat::findForUser($student);
        if ($stat === null) {
            return;
        }

        foreach ($quest->rewards as $reward) {
            $column = SkillKeys::COLUMN_MAP[$reward->stat] ?? null;
            if ($column === null) {
                continue;
            }

            $stat->{$column} = max(0, (int) $stat->{$column} - 1);
        }

        $stat->save();
    }
}
