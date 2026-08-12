<?php

namespace App\Support;

class ChildQuestAttributeBuilder
{
    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     clearCondition?: string|null,
     *     difficulty?: int|null,
     *     estimatedDuration?: string|null,
     *     questTier?: string|null
     * }  $payload
     * @return array<string, mixed>
     */
    public static function fromPayload(array $payload, bool $includeEstimatedDuration = false): array
    {
        $difficulty = QuestDifficulty::normalize($payload['difficulty'] ?? null);

        $attributes = [
            'title' => $payload['title'],
            'description' => $payload['description'] ?? '',
            'clear_condition' => $payload['clearCondition'] ?? '',
            'difficulty' => $difficulty,
            'experience_points' => QuestDifficulty::experiencePoints($difficulty),
        ];

        if ($includeEstimatedDuration) {
            $attributes['estimated_duration'] = ($payload['estimatedDuration'] ?? null) !== null
                && $payload['estimatedDuration'] !== ''
                ? (string) $payload['estimatedDuration']
                : null;
        }

        QuestTier::applyToAttributes($attributes, $payload['questTier'] ?? QuestTier::LOW);

        return $attributes;
    }
}
