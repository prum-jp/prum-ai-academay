<?php

namespace Tests\Unit;

use App\Models\Quest;
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

    public function test_import_payload_preserves_existing_difficulty_and_tier_when_omitted(): void
    {
        $existing = new Quest([
            'difficulty' => 4,
            'quest_tier' => QuestTier::EXPERT,
            'unlock_level' => QuestTier::unlockLevel(QuestTier::EXPERT),
        ]);

        $attributes = ChildQuestAttributeBuilder::fromImportPayload([
            'title' => 'Updated Quest',
        ], $existing);

        $this->assertSame(4, $attributes['difficulty']);
        $this->assertSame(QuestTier::EXPERT, $attributes['quest_tier']);
        $this->assertSame(QuestTier::unlockLevel(QuestTier::EXPERT), $attributes['unlock_level']);
    }

    public function test_import_payload_uses_default_tier_for_new_records(): void
    {
        $attributes = ChildQuestAttributeBuilder::fromImportPayload([
            'title' => 'New Quest',
        ], null, QuestTier::MEDIUM);

        $this->assertSame(QuestTier::MEDIUM, $attributes['quest_tier']);
    }
}
