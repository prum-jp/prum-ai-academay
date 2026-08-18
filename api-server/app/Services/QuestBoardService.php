<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class QuestBoardService
{
    public const PER_PAGE = 3;

    /**
     * @return Builder<Quest>
     */
    public function questQuery(User $student, string $type): Builder
    {
        return Quest::query()
            ->where('type', $type)
            ->whereNull('quest_unit_id')
            ->where('is_published', true)
            ->withCount([
                'applications as applications_count' => function ($query): void {
                    $query->whereIn('status', ['applied', 'approved']);
                },
            ])
            ->with([
                'rewards',
                'tool',
                'tools',
                'progressRecords' => function ($query) use ($student): void {
                    $query->forStudent($student)->withSubmissionPayload();
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function paginateQuests(User $student, string $type, int $page): LengthAwarePaginator
    {
        return $this->questQuery($student, $type)
            ->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page));
    }

    public function loadQuestRelations(Quest $quest, User $student): Quest
    {
        return $quest->loadCount([
            'applications as applications_count' => function ($query): void {
                $query->whereIn('status', ['applied', 'approved']);
            },
        ])->load(['rewards', 'tool', 'tools']);
    }
}
