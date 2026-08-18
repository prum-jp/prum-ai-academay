<?php

namespace App\Support;

use App\Models\StudentQuestProgress;

final class QuestSubmissionPresenter
{
    /**
     * @return array{type: string, url: string|null, text: string|null, files?: list<array{id: int, url: string|null}>}|null
     */
    public static function fromProgress(?StudentQuestProgress $progress): ?array
    {
        if ($progress === null) {
            return null;
        }

        if ($progress->submission_type === QuestSubmissionType::IMAGE) {
            return self::presentImageSubmission($progress);
        }

        return self::presentScalarSubmission($progress);
    }

    public static function hasSubmission(?StudentQuestProgress $progress): bool
    {
        return self::fromProgress($progress) !== null;
    }

    /**
     * @return array{type: string, url: string|null, text: string|null, files: list<array{id: int, url: string|null}>}|null
     */
    private static function presentImageSubmission(StudentQuestProgress $progress): ?array
    {
        $files = self::filesFromProgress($progress);
        if ($files === []) {
            return null;
        }

        return [
            'type' => QuestSubmissionType::IMAGE,
            'url' => $files[0]['url'] ?? null,
            'text' => null,
            'files' => $files,
        ];
    }

    /**
     * @return array{type: string, url: string|null, text: string|null}|null
     */
    private static function presentScalarSubmission(StudentQuestProgress $progress): ?array
    {
        $url = PublicStorage::urlForStored($progress->submission_url);
        $text = $progress->submission_text;
        $type = $progress->submission_type;

        if (($url === null || $url === '') && ($text === null || trim($text) === '')) {
            return null;
        }

        if ($type === null || $type === '') {
            $type = $url !== null && $url !== '' ? QuestSubmissionType::LINK : QuestSubmissionType::TEXT;
        }

        return [
            'type' => $type,
            'url' => $url,
            'text' => $text,
        ];
    }

    /**
     * @return list<array{id: int, url: string|null}>
     */
    private static function filesFromProgress(StudentQuestProgress $progress): array
    {
        if (! $progress->relationLoaded('submissionFiles')) {
            $progress->load('submissionFiles');
        }

        return $progress->submissionFiles
            ->map(fn ($file) => $file->toApiPayload())
            ->values()
            ->all();
    }
}
