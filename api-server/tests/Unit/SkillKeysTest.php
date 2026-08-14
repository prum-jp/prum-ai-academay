<?php

namespace Tests\Unit;

use App\Support\SkillKeys;
use Tests\TestCase;

class SkillKeysTest extends TestCase
{
    public function test_normalize_list_sorts_for_stable_comparison(): void
    {
        $this->assertSame(
            [SkillKeys::BUSINESS, SkillKeys::CONCEPTUAL],
            SkillKeys::normalizeList([SkillKeys::CONCEPTUAL, SkillKeys::BUSINESS]),
        );
    }
}
