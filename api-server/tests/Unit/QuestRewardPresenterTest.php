<?php

namespace Tests\Unit;

use App\Models\QuestReward;
use App\Support\QuestRewardPresenter;
use Tests\TestCase;

class QuestRewardPresenterTest extends TestCase
{
    public function test_stat_points_format(): void
    {
        $rewards = collect([
            new QuestReward(['stat' => 'businessSkill', 'points' => 3]),
        ]);

        $this->assertSame(
            [['stat' => 'businessSkill', 'points' => 3]],
            QuestRewardPresenter::statPoints($rewards),
        );
    }

    public function test_skill_points_format(): void
    {
        $rewards = collect([
            new QuestReward(['stat' => 'humanSkill', 'points' => 5]),
        ]);

        $this->assertSame(
            [['skill' => 'humanSkill', 'points' => 1]],
            QuestRewardPresenter::skillPoints($rewards),
        );
    }
}
