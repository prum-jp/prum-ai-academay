<?php

namespace Tests\Unit;

use App\Models\Quest;
use App\Support\QuestImportFieldResolver;
use App\Support\QuestTier;
use Tests\TestCase;

class QuestImportFieldResolverTest extends TestCase
{
    public function test_resolve_difficulty_preserves_existing_when_incoming_omitted(): void
    {
        $this->assertSame(4, QuestImportFieldResolver::resolveDifficulty(null, 4));
        $this->assertSame(4, QuestImportFieldResolver::resolveDifficulty('', 4));
    }

    public function test_resolve_difficulty_uses_incoming_when_provided(): void
    {
        $this->assertSame(2, QuestImportFieldResolver::resolveDifficulty(2, 4));
    }

    public function test_resolve_quest_tier_preserves_existing_when_incoming_omitted(): void
    {
        $quest = new Quest([
            'quest_tier' => QuestTier::HIGH,
            'unlock_level' => QuestTier::unlockLevel(QuestTier::HIGH),
        ]);

        $this->assertSame(
            QuestTier::HIGH,
            QuestImportFieldResolver::resolveQuestTier(null, $quest),
        );
    }

    public function test_resolve_quest_tier_uses_default_for_create(): void
    {
        $this->assertSame(
            QuestTier::HIGH,
            QuestImportFieldResolver::resolveQuestTier(null, null, QuestTier::HIGH),
        );
    }

    public function test_resolve_quest_tier_defaults_to_low_for_create_without_default(): void
    {
        $this->assertSame(QuestTier::LOW, QuestImportFieldResolver::resolveQuestTier(null, null));
    }
}
