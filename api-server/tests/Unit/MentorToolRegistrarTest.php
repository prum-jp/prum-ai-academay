<?php

namespace Tests\Unit;

use App\Models\Tool;
use App\Services\MentorToolRegistrar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorToolRegistrarTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_preserves_icon_when_not_provided(): void
    {
        $tool = Tool::query()->create([
            'name' => 'Gemini',
            'icon' => 'fa-solid fa-wand-magic-sparkles',
            'sort_order' => 1,
        ]);

        $registrar = new MentorToolRegistrar;
        $updated = $registrar->update($tool, ['name' => 'Google Gemini']);

        $this->assertSame('Google Gemini', $updated->name);
        $this->assertSame('fa-solid fa-wand-magic-sparkles', $updated->icon);
    }
}
