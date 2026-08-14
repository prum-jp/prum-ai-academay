<?php

namespace App\Support;

use DateTimeInterface;
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

    public static function isS3(): bool
    {
        return config('filesystems.disks.'.self::diskName().'.driver') === 's3';
    }

    public static function url(string $path): string
    {
        if (self::usesTemporaryUrls()) {
            return self::disk()->temporaryUrl($path, self::temporaryUrlExpiresAt());
        }

        return self::disk()->url($path);
    }

    public static function urlOrNull(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return self::url($path);
    }

    /**
     * Resolve a stored reference to a client-facing URL.
     * Accepts object paths, legacy S3 URLs, or external links (returned as-is).
     */
    public static function urlForStored(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }

        if (! self::isStoredOnDisk($stored)) {
            return $stored;
        }

        $path = self::resolvePathFromStored($stored);
        if ($path === null || $path === '') {
            return $stored;
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
        self::deleteStoredReference($url);
    }

    public static function deleteStoredReference(?string $stored): void
    {
        if (! self::isStoredOnDisk($stored)) {
            return;
        }

        $path = self::resolvePathFromStored($stored);
        if ($path === null || $path === '') {
            return;
        }

        self::delete($path);
    }

    public static function resolvePathFromStored(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }

        if (! str_starts_with($stored, 'http://') && ! str_starts_with($stored, 'https://')) {
            return $stored;
        }

        return self::resolvePathFromUrl($stored);
    }

    public static function isStoredOnDisk(?string $stored): bool
    {
        if ($stored === null || $stored === '') {
            return false;
        }

        if (! str_starts_with($stored, 'http://') && ! str_starts_with($stored, 'https://')) {
            return true;
        }

        return self::resolvePathFromUrl($stored) !== null;
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

        if (! self::isS3()) {
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
            return self::stripSignedUrlQuery($path);
        }

        if (str_starts_with($path, $bucket.'/')) {
            return self::stripSignedUrlQuery(substr($path, strlen($bucket) + 1));
        }

        return null;
    }

    private static function usesTemporaryUrls(): bool
    {
        if (! self::isS3()) {
            return false;
        }

        return (bool) config('filesystems.disks.'.self::diskName().'.temporary_url', true);
    }

    private static function temporaryUrlExpiresAt(): DateTimeInterface
    {
        $minutes = (int) config('filesystems.disks.'.self::diskName().'.temporary_url_minutes', 60);

        return now()->addMinutes(max($minutes, 1));
    }

    private static function stripSignedUrlQuery(string $path): string
    {
        return strtok($path, '?') ?: $path;
    }
}
