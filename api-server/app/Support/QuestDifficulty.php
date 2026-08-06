<?php

namespace App\Support;

final class QuestDifficulty
{
    public const MIN = 1;

    public const MAX = 5;

    public const XP_PER_LEVEL = 4;

    public static function normalize(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return self::clampLevel((int) $value);
        }

        if (is_string($value) && preg_match('/^\d+$/', trim($value)) === 1) {
            return self::clampLevel((int) trim($value));
        }

        if (! is_string($value)) {
            return null;
        }

        $filledStars = substr_count($value, '★');
        if ($filledStars >= self::MIN && $filledStars <= self::MAX) {
            return $filledStars;
        }

        return null;
    }

    private static function clampLevel(int $value): ?int
    {
        if ($value < self::MIN || $value > self::MAX) {
            return null;
        }

        return $value;
    }

    public static function experiencePoints(?int $difficulty): int
    {
        if ($difficulty === null) {
            return 0;
        }

        return $difficulty * self::XP_PER_LEVEL;
    }

    public static function experiencePointsFromMixed(mixed $value): int
    {
        return self::experiencePoints(self::normalize($value));
    }
}
