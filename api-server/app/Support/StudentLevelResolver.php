<?php

namespace App\Support;

use App\Models\User;
use App\Services\LevelCalculator;
use App\Services\StudentExperienceService;

class StudentLevelResolver
{
    public function __construct(
        private readonly LevelCalculator $levelCalculator,
        private readonly StudentExperienceService $studentExperienceService,
    ) {}

    public function resolve(User $user): int
    {
        $totalXp = $this->studentExperienceService->totalXp($user);

        return (int) $this->levelCalculator->calculate($totalXp)['level'];
    }
}
