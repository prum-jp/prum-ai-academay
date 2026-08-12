<?php

namespace Tests\Unit;

use App\Support\ChildQuestAttributeBuilder;
use App\Support\QuestTier;
use Tests\TestCase;

class ChildQuestAttributeBuilderTest extends TestCase
{
    public function test_builds_core_attributes_with_quest_tier(): void
    {
        $attributes = ChildQuestAttributeBuilder::fromPayload([
            'title' => 'Test Quest',
            'description' => 'Overview',
            'clearCondition' => 'Done',
            'difficulty' => 3,
            'questTier' => QuestTier::HIGH,
        ]);

        $this->assertSame('Test Quest', $attributes['title']);
        $this->assertSame('Overview', $attributes['description']);
        $this->assertSame('Done', $attributes['clear_condition']);
        $this->assertSame(3, $attributes['difficulty']);
        $this->assertSame(QuestTier::HIGH, $attributes['quest_tier']);
    }
}
