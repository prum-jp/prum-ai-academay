<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Quest;
use App\Models\StudentBadge;
use App\Models\User;

class BadgeAwarder
{
    /**
     * クエスト完了時に紐づくバッジを自動付与する。
     *
     * @return list<Badge>
     */
    public function awardForQuestCompletion(User $student, Quest $quest): array
    {
        $badges = Badge::query()
            ->where('unlock_type', Badge::UNLOCK_QUEST_COMPLETE)
            ->where('unlock_quest_id', $quest->id)
            ->get();

        $awarded = [];

        foreach ($badges as $badge) {
            $record = StudentBadge::query()->firstOrCreate(
                [
                    'user_id' => $student->id,
                    'badge_id' => $badge->id,
                ],
                [
                    'earned_at' => now(),
                ],
            );

            if ($record->wasRecentlyCreated) {
                $awarded[] = $badge;
            }
        }

        return $awarded;
    }
}
