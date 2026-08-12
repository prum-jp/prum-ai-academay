<?php

namespace Tests\Unit;

use App\Support\PublicStorage;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    public function test_public_disk_defaults_to_public(): void
    {
        Config::set('filesystems.public_disk', 'public');

        $this->assertSame('public', PublicStorage::diskName());
        $this->assertTrue(PublicStorage::isLocal());
    }

    public function test_url_or_null_returns_null_for_empty_path(): void
    {
        $this->assertNull(PublicStorage::urlOrNull(null));
        $this->assertNull(PublicStorage::urlOrNull(''));
    }

    public function test_resolve_path_from_local_url(): void
    {
        Config::set('filesystems.public_disk', 'public');
        Config::set('filesystems.disks.public.url', 'http://localhost/storage');

        $path = PublicStorage::resolvePathFromUrl('http://localhost/storage/avatars/1/test.jpg');

        $this->assertSame('avatars/1/test.jpg', $path);
    }

    public function test_resolve_path_from_external_url_returns_null_on_local_disk(): void
    {
        Config::set('filesystems.public_disk', 'public');
        Config::set('filesystems.disks.public.url', 'http://localhost/storage');

        $path = PublicStorage::resolvePathFromUrl('https://docs.google.com/spreadsheets/d/abc123');

        $this->assertNull($path);
    }

    public function test_resolve_path_from_s3_virtual_hosted_url(): void
    {
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.bucket', 'ai-academy-prd');
        Config::set('filesystems.disks.s3.region', 'ap-northeast-1');

        $path = PublicStorage::resolvePathFromUrl(
            'https://ai-academy-prd.s3.ap-northeast-1.amazonaws.com/quest-submissions/1/2/file.pdf',
        );

        $this->assertSame('quest-submissions/1/2/file.pdf', $path);
    }

    public function test_resolve_path_from_s3_path_style_url(): void
    {
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.bucket', 'ai-academy-prd');

        $path = PublicStorage::resolvePathFromUrl(
            'https://s3.ap-northeast-1.amazonaws.com/ai-academy-prd/quest-submissions/1/2/file.pdf',
        );

        $this->assertSame('quest-submissions/1/2/file.pdf', $path);
    }

    public function test_resolve_path_from_unrelated_s3_url_returns_null(): void
    {
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.bucket', 'ai-academy-prd');

        $path = PublicStorage::resolvePathFromUrl(
            'https://other-bucket.s3.ap-northeast-1.amazonaws.com/quest-submissions/1/2/file.pdf',
        );

        $this->assertNull($path);
    }

    public function test_delete_url_does_not_delete_for_external_link(): void
    {
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.bucket', 'ai-academy-prd');

        PublicStorage::deleteUrl('https://example.com/shared/document.pdf');

        $this->assertNull(PublicStorage::resolvePathFromUrl('https://example.com/shared/document.pdf'));
    }
}
