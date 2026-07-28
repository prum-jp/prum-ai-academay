<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MentorQuestCatalogService
{
    public const PER_PAGE = 3;

    /**
     * @return Builder<QuestUnit>
     */
    public function unitQuery(): Builder
    {
        return QuestUnit::query()
            ->with('rewards')
            ->withCount('quests')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function paginateUnits(int $page): LengthAwarePaginator
    {
        return $this->unitQuery()
            ->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page));
    }

    /**
     * @return Builder<Quest>
     */
    public function boardQuestQuery(string $type): Builder
    {
        return Quest::query()
            ->where('type', $type)
            ->whereNull('quest_unit_id')
            ->with('rewards')
            ->withCount([
                'applications as applications_count' => function ($query): void {
                    $query->whereIn('status', ['applied', 'approved']);
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function paginateBoardQuests(string $type, int $page): LengthAwarePaginator
    {
        return $this->boardQuestQuery($type)
            ->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page));
    }
}
