<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class PublicStorage
{
    public static function diskName(): string
    {
        return (string) config('filesystems.public_disk', 'public');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    public static function isLocal(): bool
    {
        return config('filesystems.disks.'.self::diskName().'.driver') === 'local';
    }

    public static function url(string $path): string
    {
        return self::disk()->url($path);
    }

    public static function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        self::disk()->delete($path);
    }

    public static function resolvePathFromUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $baseUrl = rtrim((string) self::disk()->url(''), '/');
        if ($baseUrl !== '' && str_starts_with($url, $baseUrl.'/')) {
            $relative = ltrim(substr($url, strlen($baseUrl)), '/');

            return $relative !== '' ? $relative : null;
        }

        $parsed = parse_url($url);
        $path = ltrim((string) ($parsed['path'] ?? ''), '/');
        if ($path === '') {
            return null;
        }

        $bucket = (string) config('filesystems.disks.s3.bucket', '');
        if ($bucket !== '' && str_starts_with($path, $bucket.'/')) {
            $path = substr($path, strlen($bucket) + 1);
        }

        return $path !== '' ? $path : null;
    }
}
