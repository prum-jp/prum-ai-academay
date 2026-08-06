<?php

namespace App\Support;

final class QuestSubmissionType
{
    public const LINK = 'link';

    public const IMAGE = 'image';

    public const VIDEO = 'video';

    public const AUDIO = 'audio';

    public const TEXT = 'text';

    /**
     * @var list<string>
     */
    public const ALL = [
        self::LINK,
        self::IMAGE,
        self::VIDEO,
        self::AUDIO,
        self::TEXT,
    ];

    /**
     * @var array<string, list<string>>
     */
    public const MIME_TYPES = [
        self::IMAGE => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        self::VIDEO => ['video/mp4', 'video/webm', 'video/quicktime'],
        self::AUDIO => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a'],
    ];

    /**
     * @var array<string, int>
     */
    public const MAX_BYTES = [
        self::IMAGE => 10 * 1024 * 1024,
        self::VIDEO => 50 * 1024 * 1024,
        self::AUDIO => 20 * 1024 * 1024,
    ];

    public static function isFileType(string $type): bool
    {
        return in_array($type, [self::IMAGE, self::VIDEO, self::AUDIO], true);
    }
}
