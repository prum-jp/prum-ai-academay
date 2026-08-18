<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PublicStorage
{
    /** @var array{path: string, exception: string, message: string, hint: string}|null */
    private static ?array $lastUrlError = null;

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
        $url = self::resolveUrl($path);
        if ($url === null) {
            $hint = self::$lastUrlError['hint'] ?? 'ストレージ URL の生成に失敗しました。';

            throw new \RuntimeException($hint);
        }

        return $url;
    }

    public static function urlOrNull(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return self::resolveUrl($path);
    }

    /**
     * @return array{path: string, exception: string, message: string, hint: string}|null
     */
    public static function lastUrlError(): ?array
    {
        return self::$lastUrlError;
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

        return self::resolveUrl($path);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function appendLastUrlErrorTo(array $data, string $key = 'avatarUrlError'): array
    {
        $urlError = self::lastUrlError();
        if ($urlError === null) {
            return $data;
        }

        $data[$key] = config('app.debug')
            ? $urlError
            : ['hint' => $urlError['hint']];

        return $data;
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

    private static function resolveUrl(string $path): ?string
    {
        self::$lastUrlError = null;

        try {
            if (self::usesTemporaryUrls()) {
                return self::disk()->temporaryUrl($path, self::temporaryUrlExpiresAt());
            }

            return self::disk()->url($path);
        } catch (Throwable $exception) {
            self::recordUrlError($path, $exception);

            return null;
        }
    }

    private static function recordUrlError(string $path, Throwable $exception): void
    {
        $hint = self::diagnoseUrlFailure($exception);

        self::$lastUrlError = [
            'path' => $path,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'hint' => $hint,
        ];

        Log::warning('[PublicStorage] Failed to resolve public file URL.', [
            'path' => $path,
            'disk' => self::diskName(),
            'bucket' => config('filesystems.disks.'.self::diskName().'.bucket'),
            'region' => config('filesystems.disks.'.self::diskName().'.region'),
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'hint' => $hint,
        ]);
    }

    private static function diagnoseUrlFailure(Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'AccessDenied') || str_contains($message, '403')) {
            return 'S3 の参照権限 (s3:GetObject) または署名 URL 生成権限を確認してください。';
        }

        if (str_contains($message, 'InvalidAccessKeyId')) {
            return 'AWS_ACCESS_KEY_ID が正しくありません。Lightsail の環境変数を確認してください。';
        }

        if (str_contains($message, 'SignatureDoesNotMatch')) {
            return 'AWS_SECRET_ACCESS_KEY が正しくありません。Lightsail の環境変数を確認してください。';
        }

        if (str_contains($message, 'NoSuchKey') || str_contains($message, '404')) {
            return 'S3 上にファイルが存在しません。アップロード失敗または手動削除の可能性があります。';
        }

        if (str_contains($message, 'PermanentRedirect') || str_contains($message, 'AuthorizationHeaderMalformed')) {
            return 'AWS_DEFAULT_REGION または AWS_BUCKET の設定を確認してください。';
        }

        return 'ストレージ URL の生成に失敗しました。詳細はサーバーログを確認してください。';
    }
}
