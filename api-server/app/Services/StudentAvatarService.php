<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        $path = $file->store('avatars/'.$user->id, 'public');

        $profile->update([
            'avatar_path' => $path,
        ]);

        if ($previousPath && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return $path;
    }

    public function delete(User $user): void
    {
        $profile = $user->studentProfile;

        if ($profile?->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($profile->avatar_path);
        $profile->update([
            'avatar_path' => null,
        ]);
    }
}
