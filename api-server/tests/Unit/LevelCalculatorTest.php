<?php

namespace Tests\Unit;

use App\Services\LevelCalculator;
use PHPUnit\Framework\TestCase;

class LevelCalculatorTest extends TestCase
{
    public function test_calculates_level_from_cumulative_xp_thresholds(): void
    {
        $calculator = new LevelCalculator();

        $this->assertSame(1, $calculator->calculate(0)['level']);
        $this->assertSame(2, $calculator->calculate(1)['level']);
        $this->assertSame(3, $calculator->calculate(8)['level']);
        $this->assertSame(15, $calculator->calculate(2744)['level']);
        $this->assertSame('Lv.5 プレイヤー I', $calculator->calculate(64)['level_title']);
    }

    public function test_progress_percent_within_level(): void
    {
        $calculator = new LevelCalculator();

        $this->assertSame(0.0, $calculator->calculate(1)['progress_percent']);
        $this->assertSame(100.0, $calculator->calculate(2744)['progress_percent']);
    }
}
