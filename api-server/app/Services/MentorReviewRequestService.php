<?php

namespace App\Services;

use App\Models\StudentQuestProgress;
use App\Support\QuestProgressStatus;
use Illuminate\Database\Eloquent\Collection;

class MentorReviewRequestService
{
    /**
     * @return Collection<int, StudentQuestProgress>
     */
    public function listReviewRequests(): Collection
    {
        return StudentQuestProgress::query()
            ->where('status', QuestProgressStatus::REVIEW_REQUESTED)
            ->with([
                'user:id,name',
                'quest:id,title,sort_order',
            ])
            ->orderByDesc('updated_at')
            ->get();
    }
}
