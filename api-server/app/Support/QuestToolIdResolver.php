<?php

namespace App\Support;

class QuestToolIdResolver
{
    /**
     * @param  array{toolId?: int|null, toolIds?: list<int|null>}  $payload
     * @return list<int>
     */
    public static function resolve(array $payload): array
    {
        $toolIds = $payload['toolIds'] ?? [];
        if ($toolIds !== []) {
            return array_values(array_filter(
                array_map(static fn ($id) => $id !== null ? (int) $id : null, $toolIds),
                static fn ($id) => $id !== null && $id > 0,
            ));
        }

        if (($payload['toolId'] ?? null) !== null) {
            return [(int) $payload['toolId']];
        }

        return [];
    }
}
