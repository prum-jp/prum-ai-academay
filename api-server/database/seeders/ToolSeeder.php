<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            [
                'code' => 'gemini',
                'name' => 'Gemini',
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'sort_order' => 1,
            ],
            [
                'code' => 'notebooklm',
                'name' => 'NotebookLM',
                'icon' => 'fa-solid fa-book-open',
                'sort_order' => 2,
            ],
        ];

        foreach ($tools as $tool) {
            Tool::query()->updateOrCreate(
                ['code' => $tool['code']],
                $tool,
            );
        }
    }
}
