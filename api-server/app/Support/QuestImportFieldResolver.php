<?php

namespace App\Support;

use App\Models\Quest;

final class QuestImportFieldResolver
{
    public static function resolveDifficulty(mixed $incoming, ?int $existing): ?int
    {
        $normalized = QuestDifficulty::normalize($incoming);

        return $normalized ?? $existing;
    }

    public static function resolveQuestTier(
        mixed $incoming,
        ?Quest $existing,
        ?string $defaultForCreate = null,
    ): string {
        if ($incoming === null || $incoming === '') {
            if ($existing !== null) {
                return QuestTier::resolve(
                    $existing->quest_tier,
                    $existing->unlock_level !== null ? (int) $existing->unlock_level : null,
                );
            }

            return $defaultForCreate ?? QuestTier::LOW;
        }

        return QuestTier::normalize($incoming);
    }
}
