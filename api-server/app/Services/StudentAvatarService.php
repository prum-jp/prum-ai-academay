<?php

namespace App\Services;

use App\Models\User;
use App\Support\PublicStorage;
use Illuminate\Http\UploadedFile;

class StudentAvatarService
{
    public const MAX_KILOBYTES = 5120;

    public function store(User $user, UploadedFile $file): string
    {
        $profile = $user->studentProfile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'background' => '',
                'hobby' => '',
                'weapon_skill' => '',
                'spell_goal' => '',
            ],
        );

        $previousPath = $profile->avatar_path;
        $disk = PublicStorage::diskName();
        $path = $file->store('avatars/'.$user->id, $disk);

        $profile->update([
            'avatar_path' => $path,
        ]);

        if ($previousPath && $previousPath !== $path) {
            PublicStorage::delete($previousPath);
        }

        return $path;
    }

    public function delete(User $user): void
    {
        $profile = $user->studentProfile;

        if ($profile?->avatar_path === null) {
            return;
        }

        PublicStorage::delete($profile->avatar_path);
        $profile->update([
            'avatar_path' => null,
        ]);
    }
}
