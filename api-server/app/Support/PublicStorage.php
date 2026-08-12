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

    public static function urlOrNull(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return self::url($path);
    }

    public static function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        self::disk()->delete($path);
    }

    public static function deleteUrl(?string $url): void
    {
        $path = self::resolvePathFromUrl($url);
        if ($path === null) {
            return;
        }

        self::delete($path);
    }

    public static function resolvePathFromUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (self::isLocal()) {
            $baseUrl = rtrim((string) self::disk()->url(''), '/');
            if ($baseUrl !== '' && str_starts_with($url, $baseUrl.'/')) {
                $relative = ltrim(substr($url, strlen($baseUrl)), '/');

                return $relative !== '' ? $relative : null;
            }

            return null;
        }

        if (config('filesystems.disks.'.self::diskName().'.driver') !== 's3') {
            return null;
        }

        $bucket = (string) config('filesystems.disks.'.self::diskName().'.bucket', '');
        if ($bucket === '') {
            return null;
        }

        $parsed = parse_url($url);
        if ($parsed === false) {
            return null;
        }

        $host = strtolower((string) ($parsed['host'] ?? ''));
        $path = ltrim((string) ($parsed['path'] ?? ''), '/');
        if ($path === '') {
            return null;
        }

        if (str_starts_with($host, $bucket.'.s3.') || str_starts_with($host, $bucket.'.s3-')) {
            return $path;
        }

        if (str_starts_with($path, $bucket.'/')) {
            return substr($path, strlen($bucket) + 1);
        }

        return null;
    }
}
