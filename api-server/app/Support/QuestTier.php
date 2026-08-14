<?php

namespace App\Support;

final class QuestTier
{
    public const LOW = 'low';

    public const MEDIUM = 'medium';

    public const HIGH = 'high';

    public const EXPERT = 'expert';

    /**
     * @var list<string>
     */
    public const ALL = [
        self::LOW,
        self::MEDIUM,
        self::HIGH,
        self::EXPERT,
    ];

    /**
     * @var array<string, int|null>
     */
    public const UNLOCK_LEVELS = [
        self::LOW => null,
        self::MEDIUM => 6,
        self::HIGH => 9,
        self::EXPERT => 13,
    ];

    /**
     * @var array<string, string>
     */
    public const LABELS = [
        self::LOW => '低クエスト',
        self::MEDIUM => '中クエスト',
        self::HIGH => '高クエスト',
        self::EXPERT => 'エキスパートクエスト',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const ALIASES = [
        self::LOW => ['low', '低', '低クエスト', 'ロー'],
        self::MEDIUM => ['medium', '中', '中クエスト', 'ミドル'],
        self::HIGH => ['high', '高', '高クエスト', 'ハイ'],
        self::EXPERT => ['expert', 'エキスパート', 'エキスパートクエスト', '上級'],
    ];

    public static function normalize(mixed $value): string
    {
        if ($value === null || $value === '') {
            return self::LOW;
        }

        $normalized = mb_strtolower(trim((string) $value));

        if (in_array($normalized, self::ALL, true)) {
            return $normalized;
        }

        foreach (self::ALIASES as $tier => $aliases) {
            foreach ($aliases as $alias) {
                if ($normalized === mb_strtolower($alias)) {
                    return $tier;
                }
            }
        }

        return self::LOW;
    }

    public static function isRecognized(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $normalized = mb_strtolower(trim((string) $value));

        if (in_array($normalized, self::ALL, true)) {
            return true;
        }

        foreach (self::ALIASES as $aliases) {
            foreach ($aliases as $alias) {
                if ($normalized === mb_strtolower($alias)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function unlockLevel(string $tier): ?int
    {
        return self::UNLOCK_LEVELS[$tier] ?? null;
    }

    public static function label(string $tier): string
    {
        return self::LABELS[$tier] ?? self::LABELS[self::LOW];
    }

    public static function fromUnlockLevel(?int $unlockLevel): string
    {
        if ($unlockLevel === null) {
            return self::LOW;
        }

        foreach (self::UNLOCK_LEVELS as $tier => $level) {
            if ($level === $unlockLevel) {
                return $tier;
            }
        }

        return self::LOW;
    }

    public static function resolve(?string $storedTier, ?int $unlockLevel): string
    {
        if ($storedTier !== null && in_array($storedTier, self::ALL, true)) {
            return $storedTier;
        }

        return self::fromUnlockLevel($unlockLevel);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function applyToAttributes(array &$attributes, mixed $tierInput): void
    {
        $tier = self::normalize($tierInput);
        $attributes['quest_tier'] = $tier;
        $attributes['unlock_level'] = self::unlockLevel($tier);
    }
}
