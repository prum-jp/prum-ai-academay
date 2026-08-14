<?php

namespace App\Services;

use App\Models\Tool;

class MentorToolRegistrar
{
    /**
     * @param  array{name: string, icon?: string|null}  $payload
     */
    public function register(array $payload): Tool
    {
        $maxSortOrder = (int) Tool::query()->max('sort_order');

        return Tool::query()->create([
            'name' => trim($payload['name']),
            'icon' => $payload['icon'] ?? null,
            'sort_order' => $maxSortOrder + 1,
        ]);
    }

    /**
     * @param  array{name: string, icon?: string|null}  $payload
     */
    public function update(Tool $tool, array $payload): Tool
    {
        $tool->update([
            'name' => trim($payload['name']),
            'icon' => array_key_exists('icon', $payload) ? $payload['icon'] : $tool->icon,
        ]);

        return $tool->fresh();
    }
}
