<?php

namespace App\Services;

use App\Models\Tool;

class MentorToolRegistrar
{
    /**
     * @param  array{code: string, name: string, icon?: string|null}  $payload
     */
    public function register(array $payload): Tool
    {
        $maxSortOrder = (int) Tool::query()->max('sort_order');

        return Tool::query()->create([
            'code' => $payload['code'],
            'name' => $payload['name'],
            'icon' => $payload['icon'] ?? null,
            'sort_order' => $maxSortOrder + 1,
        ]);
    }
}
