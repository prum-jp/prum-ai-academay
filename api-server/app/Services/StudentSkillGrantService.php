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

        $stat = $this->ensureStudentStat($student);

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

        $stat = $student->studentStat;
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

    private function ensureStudentStat(User $student): StudentStat
    {
        $stat = $student->studentStat;

        if ($stat !== null) {
            return $stat;
        }

        return StudentStat::query()->create([
            'user_id' => $student->id,
            'stat_business_skill' => 0,
            'stat_human_skill' => 0,
            'stat_conceptual_skill' => 0,
            'total_xp' => 0,
        ]);
    }
}
