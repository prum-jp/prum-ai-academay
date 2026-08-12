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

    public function test_resolve_path_from_local_url(): void
    {
        Config::set('filesystems.public_disk', 'public');
        Config::set('filesystems.disks.public.url', 'http://localhost/storage');

        $path = PublicStorage::resolvePathFromUrl('http://localhost/storage/avatars/1/test.jpg');

        $this->assertSame('avatars/1/test.jpg', $path);
    }
}
