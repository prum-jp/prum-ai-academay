<?php

namespace Tests\Unit;

use App\Support\QuestDescriptionSections;
use Tests\TestCase;

class QuestDescriptionSectionsTest extends TestCase
{
    public function test_same_content_treats_procedure_marker_as_merged_overview(): void
    {
        $storedDescription = "概要テキスト\n\n【内容・進め方】\n進め方テキスト\n\n【目的】\n目的テキスト";
        $importDescription = "概要テキスト\n\n進め方テキスト\n\n【目的】\n目的テキスト";

        $this->assertTrue(QuestDescriptionSections::sameContent(
            $importDescription,
            '',
            $storedDescription,
            '',
        ));
    }

    public function test_same_content_compares_deliverable_section(): void
    {
        $leftClear = "完了条件\n【提出物】\nレポート";
        $rightClear = "完了条件\n【提出物】\nレポート";

        $this->assertTrue(QuestDescriptionSections::sameContent('', $leftClear, '', $rightClear));
    }

    public function test_same_content_detects_purpose_difference(): void
    {
        $storedDescription = "概要\n\n【目的】\n目的A";
        $importDescription = "概要\n\n【目的】\n目的B";

        $this->assertFalse(QuestDescriptionSections::sameContent(
            $importDescription,
            '',
            $storedDescription,
            '',
        ));
    }
}
