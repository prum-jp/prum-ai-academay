<?php

namespace App\Services;

use App\Models\QuestUnit;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class QuestUnitService
{
    public const PER_PAGE = 3;

    public function paginateForStudent(User $student, int $page): LengthAwarePaginator
    {
        return $this->unitQuery($student)
            ->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page));
    }

    /**
     * @return Builder<QuestUnit>
     */
    private function unitQuery(User $student): Builder
    {
        return QuestUnit::query()
            ->where('is_published', true)
            ->with([
                'rewards',
                'quests' => function ($query) use ($student): void {
                    $query
                        ->where('type', 'personal')
                        ->where('is_published', true)
                        ->with([
                            'tool',
                            'progressRecords' => function ($progressQuery) use ($student): void {
                                $progressQuery->where('user_id', $student->id);
                            },
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
