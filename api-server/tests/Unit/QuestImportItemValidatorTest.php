<?php

namespace Tests\Unit;

use App\Services\QuestImport\QuestImportItemValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestImportItemValidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_unrecognized_quest_tier(): void
    {
        $validator = new QuestImportItemValidator;

        $errors = $validator->validateItem([
            'kind' => 'child_quest',
            'title' => 'Sample Quest',
            'unitTitle' => 'Unit A',
            'questTier' => 'invalid-tier',
            'skillGrants' => [],
            '_batchUnitTitles' => ['Unit A'],
        ]);

        $this->assertContains('クエストTier が不正です。', $errors);
    }
}
