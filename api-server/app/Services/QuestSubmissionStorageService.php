<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use App\Support\QuestSubmissionType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestSubmissionStorageService
{
    public function store(User $student, Quest $quest, UploadedFile $file, string $type): string
    {
        $extension = $file->guessExtension() ?: 'bin';
        $filename = Str::uuid()->toString().'.'.$extension;
        $directory = "quest-submissions/{$student->id}/{$quest->id}";

        $path = $file->storeAs($directory, $filename, 'public');

        return Storage::disk('public')->url($path);
    }

    public function deleteStoredUrl(?string $url): void
    {
        if ($url === null || $url === '') {
            return;
        }

        $path = $this->resolvePublicDiskPath($url);
        if ($path === null) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function resolvePublicDiskPath(string $url): ?string
    {
        $baseUrl = rtrim((string) Storage::disk('public')->url(''), '/');
        if (! str_starts_with($url, $baseUrl.'/')) {
            return null;
        }

        $relative = ltrim(substr($url, strlen($baseUrl)), '/');

        return $relative !== '' ? $relative : null;
    }
}
