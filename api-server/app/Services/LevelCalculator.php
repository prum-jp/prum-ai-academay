<?php

namespace App\Services;

class LevelCalculator
{
    public const MAX_LEVEL = 15;

    /**
     * @var array<int, string>
     */
    private const TITLES = [
        1 => 'ビギナー I',
        2 => 'ビギナー II',
        3 => 'ビギナー III',
        4 => 'ビギナー IV',
        5 => 'プレイヤー I',
        6 => 'プレイヤー II',
        7 => 'プレイヤー III',
        8 => 'プレイヤー IV',
        9 => 'エキスパート I',
        10 => 'エキスパート II',
        11 => 'エキスパート III',
        12 => 'エキスパート IV',
        13 => 'マスター I',
        14 => 'マスター II',
        15 => 'マスター III',
    ];

    /**
     * @return array{
     *     level: int,
     *     title: string,
     *     level_title: string,
     *     total: int,
     *     progress_percent: float,
     *     xp_current_level_min: int,
     *     xp_next_level_min: int|null
     * }
     */
    public function calculate(int $totalXp): array
    {
        $totalXp = max(0, $totalXp);
        $level = 1;

        for ($candidateLevel = self::MAX_LEVEL; $candidateLevel >= 1; $candidateLevel--) {
            if ($totalXp >= $this->cumulativeXpForLevel($candidateLevel)) {
                $level = $candidateLevel;
                break;
            }
        }

        $title = self::TITLES[$level];
        $currentMin = $this->cumulativeXpForLevel($level);
        $nextMin = $level < self::MAX_LEVEL ? $this->cumulativeXpForLevel($level + 1) : null;

        return [
            'level' => $level,
            'title' => $title,
            'level_title' => sprintf('Lv.%d %s', $level, $title),
            'total' => $totalXp,
            'progress_percent' => $this->progressPercent($totalXp, $level, $currentMin, $nextMin),
            'xp_current_level_min' => $currentMin,
            'xp_next_level_min' => $nextMin,
        ];
    }

    public function cumulativeXpForLevel(int $level): int
    {
        $level = max(1, min(self::MAX_LEVEL, $level));

        return ($level - 1) ** 3;
    }

    private function progressPercent(int $totalXp, int $level, int $currentMin, ?int $nextMin): float
    {
        if ($level >= self::MAX_LEVEL || $nextMin === null) {
            return 100.0;
        }

        $range = $nextMin - $currentMin;
        if ($range <= 0) {
            return 100.0;
        }

        $progress = (($totalXp - $currentMin) / $range) * 100;

        return round(min(100, max(0, $progress)), 1);
    }
}
