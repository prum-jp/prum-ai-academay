<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\StudentStat;
use App\Models\User;
use App\Support\QuestProgressStatus;

class StudentExperienceService
{
    public function totalXp(User $student): int
    {
        $student->loadMissing('studentStat');

        return (int) ($student->studentStat?->total_xp ?? 0);
    }

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
        $points = (int) ($quest->experience_points ?? 0);
        if ($points <= 0) {
            return;
        }

        $stat = $this->ensureStudentStat($student);
        $stat->total_xp = (int) $stat->total_xp + $points;
        $stat->save();
    }

    private function revoke(User $student, Quest $quest): void
    {
        $points = (int) ($quest->experience_points ?? 0);
        if ($points <= 0) {
            return;
        }

        $stat = $this->ensureStudentStat($student);
        $stat->total_xp = max(0, (int) $stat->total_xp - $points);
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
