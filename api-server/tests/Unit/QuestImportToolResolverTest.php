<?php

namespace Tests\Unit;

use App\Services\QuestImport\QuestImportToolResolver;
use App\Services\MentorToolRegistrar;
use PHPUnit\Framework\TestCase;

class QuestImportToolResolverTest extends TestCase
{
    private QuestImportToolResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new QuestImportToolResolver(new MentorToolRegistrar());
    }

    public function test_split_tool_names_with_comma(): void
    {
        $this->assertSame(
            ['Gemini', 'Googleスプレッドシート'],
            $this->resolver->splitToolNames('Gemini, Googleスプレッドシート'),
        );
    }

    public function test_split_tool_names_with_space_between_latin_and_japanese(): void
    {
        $this->assertSame(
            ['Gemini', 'Googleスプレッドシート'],
            $this->resolver->splitToolNames('Gemini Googleスプレッドシート'),
        );
    }

    public function test_does_not_split_japanese_tool_name_with_internal_space(): void
    {
        $this->assertSame(
            ['Google スプレッドシート'],
            $this->resolver->splitToolNames('Google スプレッドシート'),
        );
    }

    public function test_split_tool_names_with_slash(): void
    {
        $this->assertSame(
            ['Gemini', 'NotebookLM'],
            $this->resolver->splitToolNames('Gemini / NotebookLM'),
        );
    }
}
