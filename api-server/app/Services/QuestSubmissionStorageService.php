<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use App\Support\PublicStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class QuestSubmissionStorageService
{
    public function store(User $student, Quest $quest, UploadedFile $file, string $type): string
    {
        $extension = $file->guessExtension() ?: 'bin';
        $filename = Str::uuid()->toString().'.'.$extension;
        $directory = "quest-submissions/{$student->id}/{$quest->id}";

        $path = $file->storeAs($directory, $filename, PublicStorage::diskName());

        return PublicStorage::url($path);
    }

    public function deleteStoredUrl(?string $url): void
    {
        $path = PublicStorage::resolvePathFromUrl($url);
        if ($path === null) {
            return;
        }

        PublicStorage::delete($path);
    }
}
