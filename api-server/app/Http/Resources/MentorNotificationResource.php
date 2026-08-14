<?php

namespace App\Http\Resources;

use App\Models\MentorNotification;
use App\Support\MentorNotificationType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MentorNotification
 */
class MentorNotificationResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $metadata = $this->metadata ?? [];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'message' => $this->buildMessage($metadata),
            'studentId' => isset($metadata['studentId']) ? (int) $metadata['studentId'] : null,
            'questId' => isset($metadata['questId']) ? (int) $metadata['questId'] : null,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function buildMessage(array $metadata): string
    {
        $studentName = (string) ($metadata['studentName'] ?? '受講生');

        return match ($this->type) {
            MentorNotificationType::COMMENT => sprintf(
                '%sがクエスト「%s」にコメントしました',
                $studentName,
                $metadata['questTitle'] ?? '',
            ),
            MentorNotificationType::REVIEW_REQUESTED => sprintf(
                '%sがクエスト「%s」のレビューを依頼しました',
                $studentName,
                $metadata['questTitle'] ?? '',
            ),
            default => '新しい通知があります',
        };
    }
}
