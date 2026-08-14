<?php

namespace Tests\Unit;

use App\Support\QuestTier;
use Tests\TestCase;

class QuestTierTest extends TestCase
{
    public function test_is_recognized_accepts_canonical_and_alias_values(): void
    {
        $this->assertTrue(QuestTier::isRecognized('high'));
        $this->assertTrue(QuestTier::isRecognized('高クエスト'));
        $this->assertFalse(QuestTier::isRecognized(''));
        $this->assertFalse(QuestTier::isRecognized(null));
        $this->assertFalse(QuestTier::isRecognized('invalid-tier'));
    }
}
