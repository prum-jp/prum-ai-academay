<?php

namespace Tests\Unit;

use App\Support\PublicStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

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

    public function test_resolve_path_from_signed_s3_url_strips_query(): void
    {
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.bucket', 'ai-academy-prd');

        $path = PublicStorage::resolvePathFromUrl(
            'https://ai-academy-prd.s3.ap-northeast-1.amazonaws.com/avatars/1/test.jpg?X-Amz-Signature=abc',
        );

        $this->assertSame('avatars/1/test.jpg', $path);
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

    public function test_url_for_stored_returns_external_link_as_is(): void
    {
        $url = PublicStorage::urlForStored('https://docs.google.com/document/d/abc');

        $this->assertSame('https://docs.google.com/document/d/abc', $url);
    }

    public function test_url_for_stored_uses_temporary_url_for_s3_path(): void
    {
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.temporary_url', true);
        Config::set('filesystems.disks.s3.temporary_url_minutes', 60);

        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('temporaryUrl')
            ->once()
            ->with('avatars/1/test.jpg', Mockery::type(Carbon::class))
            ->andReturn('https://signed.example/avatars/1/test.jpg');

        Storage::shouldReceive('disk')->with('s3')->andReturn($disk);

        $url = PublicStorage::urlForStored('avatars/1/test.jpg');

        $this->assertSame('https://signed.example/avatars/1/test.jpg', $url);
    }

    public function test_url_or_null_records_error_when_temporary_url_fails(): void
    {
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.temporary_url', true);

        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('temporaryUrl')
            ->once()
            ->with('avatars/1/test.jpg', Mockery::type(Carbon::class))
            ->andThrow(new \RuntimeException('AccessDenied'));

        Storage::shouldReceive('disk')->with('s3')->andReturn($disk);

        $this->assertNull(PublicStorage::urlOrNull('avatars/1/test.jpg'));

        $error = PublicStorage::lastUrlError();
        $this->assertNotNull($error);
        $this->assertSame('avatars/1/test.jpg', $error['path']);
        $this->assertStringContainsString('GetObject', $error['hint']);
    }

    public function test_append_last_url_error_to_includes_hint_when_not_debug(): void
    {
        Config::set('app.debug', false);
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.temporary_url', true);

        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('temporaryUrl')
            ->once()
            ->andThrow(new \RuntimeException('AccessDenied'));

        Storage::shouldReceive('disk')->with('s3')->andReturn($disk);

        PublicStorage::urlOrNull('avatars/1/test.jpg');

        $data = PublicStorage::appendLastUrlErrorTo(['avatarUrl' => null]);

        $this->assertArrayHasKey('avatarUrlError', $data);
        $this->assertSame(
            ['hint' => 'S3 の参照権限 (s3:GetObject) または署名 URL 生成権限を確認してください。'],
            $data['avatarUrlError'],
        );
    }

    public function test_is_stored_on_disk_distinguishes_external_links(): void
    {
        Config::set('filesystems.public_disk', 's3');
        Config::set('filesystems.disks.s3.driver', 's3');
        Config::set('filesystems.disks.s3.bucket', 'ai-academy-prd');

        $this->assertTrue(PublicStorage::isStoredOnDisk('quest-submissions/1/2/file.pdf'));
        $this->assertFalse(PublicStorage::isStoredOnDisk('https://example.com/doc.pdf'));
    }
}
