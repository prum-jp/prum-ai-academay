<?php

namespace App\Services;

class LevelCalculator
{
    /**
     * @var array<int, array{min: int, max: int|null, title: string}>
     */
    private const LEVELS = [
        1 => ['min' => 0, 'max' => 4, 'title' => '駆け出し勇者'],
        2 => ['min' => 5, 'max' => 11, 'title' => '見習い魔法使い'],
        3 => ['min' => 12, 'max' => 21, 'title' => '学びの探検家'],
        4 => ['min' => 22, 'max' => 34, 'title' => '自走の魔術師'],
        5 => ['min' => 35, 'max' => 49, 'title' => 'AIバトラー'],
        6 => ['min' => 50, 'max' => null, 'title' => '爆速の賢者'],
    ];

    /**
     * @return array{level: int, title: string, level_title: string, total: int, progress_percent: float}
     */
    public function calculate(int $total): array
    {
        $level = 1;

        foreach (self::LEVELS as $candidateLevel => $definition) {
            $min = $definition['min'];
            $max = $definition['max'];

            if ($total >= $min && ($max === null || $total <= $max)) {
                $level = $candidateLevel;
                break;
            }
        }

        $title = self::LEVELS[$level]['title'];

        return [
            'level' => $level,
            'title' => $title,
            'level_title' => sprintf('LV.%d %s', $level, $title),
            'total' => $total,
            'progress_percent' => $this->progressPercent($total, $level),
        ];
    }

    private function progressPercent(int $total, int $level): float
    {
        if ($level >= 6) {
            return 100.0;
        }

        $currentMin = self::LEVELS[$level]['min'];
        $nextMin = self::LEVELS[$level + 1]['min'];
        $range = $nextMin - $currentMin;

        if ($range <= 0) {
            return 100.0;
        }

        $progress = (($total - $currentMin) / $range) * 100;

        return round(min(100, max(0, $progress)), 1);
    }
}
