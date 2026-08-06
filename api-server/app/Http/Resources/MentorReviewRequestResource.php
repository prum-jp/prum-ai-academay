<?php

namespace App\Http\Resources;

use App\Models\StudentQuestProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StudentQuestProgress
 */
class MentorReviewRequestResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'studentId' => $this->user_id,
            'studentName' => $this->user?->name ?? '',
            'questId' => $this->quest_id,
            'questTitle' => $this->quest?->title ?? '',
            'type' => 'review_requested',
            'typeLabel' => 'レビュー依頼',
            'requestedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
