<?php

namespace App\Support;

use App\Models\StudentQuestProgress;

final class QuestSubmissionPresenter
{
    /**
     * @return array{type: string, url: string|null, text: string|null}|null
     */
    public static function fromProgress(?StudentQuestProgress $progress): ?array
    {
        if ($progress === null) {
            return null;
        }

        $url = $progress->submission_url;
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

    public static function hasSubmission(?StudentQuestProgress $progress): bool
    {
        return self::fromProgress($progress) !== null;
    }
}
