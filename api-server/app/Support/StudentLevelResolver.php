<?php

namespace App\Support;

use App\Models\User;
use App\Services\LevelCalculator;

class StudentLevelResolver
{
    public function __construct(
        private readonly LevelCalculator $levelCalculator,
    ) {}

    public function resolve(User $user): int
    {
        $user->loadMissing('studentStat');
        $stat = $user->studentStat;
        $total = (int) ($stat?->stat_presentation ?? 0)
            + (int) ($stat?->stat_communication ?? 0)
            + (int) ($stat?->stat_problem_finding ?? 0)
            + (int) ($stat?->stat_ai_affinity ?? 0)
            + (int) ($stat?->stat_action ?? 0)
            + (int) ($stat?->stat_support ?? 0);

        return (int) $this->levelCalculator->calculate($total)['level'];
    }
}
