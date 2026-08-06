<?php

namespace App\Http\Resources;

use App\Models\StudentNotification;
use App\Support\QuestProgressStatus;
use App\Support\StudentNotificationType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StudentNotification
 */
class StudentNotificationResource extends JsonResource
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
            'questId' => $metadata['questId'] ?? null,
            'curriculumId' => $metadata['curriculumId'] ?? null,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function buildMessage(array $metadata): string
    {
        $actorName = (string) ($metadata['actorName'] ?? 'メンター');

        return match ($this->type) {
            StudentNotificationType::CURRICULUM_ADDED => sprintf(
                '%sがカリキュラム「%s」を追加しました',
                $actorName,
                $metadata['curriculumName'] ?? '',
            ),
            StudentNotificationType::STATUS_CHANGED => sprintf(
                '%sがクエスト「%s」のステータスを「%s」に変更しました',
                $actorName,
                $metadata['questTitle'] ?? '',
                $this->statusLabel((string) ($metadata['toStatus'] ?? '')),
            ),
            StudentNotificationType::COMMENT => sprintf(
                '%sがクエスト「%s」にコメントしました',
                $actorName,
                $metadata['questTitle'] ?? '',
            ),
            default => '新しい通知があります',
        };
    }

    private function statusLabel(string $status): string
    {
        return match (QuestProgressStatus::normalize($status)) {
            QuestProgressStatus::NOT_STARTED => '未着手',
            QuestProgressStatus::IN_PROGRESS => '着手中',
            QuestProgressStatus::REVIEW_REQUESTED => 'レビュー依頼中',
            QuestProgressStatus::REJECTED => '差し戻し',
            QuestProgressStatus::COMPLETED => '完了',
            default => $status,
        };
    }
}
