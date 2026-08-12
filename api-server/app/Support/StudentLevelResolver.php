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
        return (int) $this->resolvePayload($user)['level'];
    }

    /**
     * @return array{
     *     level: int,
     *     level_title: string,
     *     total: int,
     *     progress_percent: int,
     *     xp_current_level_min: int,
     *     xp_next_level_min: int|null
     * }
     */
    public function resolvePayload(User $user): array
    {
        $totalXp = $this->studentExperienceService->totalXp($user);

        return $this->levelCalculator->calculate($totalXp);
    }

    public function resolveAvatarUrl(User $user): ?string
    {
        return PublicStorage::urlOrNull($user->studentProfile?->avatar_path);
    }
}
