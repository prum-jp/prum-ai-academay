<?php

namespace App\Services;

use App\Models\QuestUnit;
use App\Models\User;
use App\Support\QuestProgressStatus;
use App\Support\QuestUnitProgressStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class QuestUnitService
{
    public const PER_PAGE = 3;

    /**
     * @var list<string>
     */
    public const PROGRESS_FILTERS = [
        'all',
        'in_progress',
        'completed',
        'not_started',
    ];

    public function __construct(
        private readonly StudentQuestAssignmentQuery $assignmentQuery,
    ) {}

    public function paginateForStudent(User $student, int $page, ?string $progressFilter = null): LengthAwarePaginator
    {
        $units = $this->unitQuery($student)->get();
        $normalizedFilter = $this->normalizeProgressFilter($progressFilter);
        $filtered = $this->filterUnitsByProgress($units, $normalizedFilter);

        return $this->paginateCollection($filtered, max(1, $page), self::PER_PAGE);
    }

    /**
     * @return Builder<QuestUnit>
     */
    private function unitQuery(User $student): Builder
    {
        $assignedQuestIds = $this->assignmentQuery
            ->assignedPersonalQuests($student)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $this->assignmentQuery
            ->assignedUnits($student)
            ->with([
                'rewards',
                'quests' => function ($query) use ($student, $assignedQuestIds): void {
                    $query
                        ->where('type', 'personal')
                        ->when(
                            $assignedQuestIds !== [],
                            fn ($questQuery) => $questQuery->whereIn('id', $assignedQuestIds),
                            fn ($questQuery) => $questQuery->whereRaw('0 = 1'),
                        )
                        ->with([
                            'rewards',
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

    private function normalizeProgressFilter(?string $progressFilter): string
    {
        if ($progressFilter === null || $progressFilter === '' || $progressFilter === 'all') {
            return 'all';
        }

        return in_array($progressFilter, self::PROGRESS_FILTERS, true)
            ? $progressFilter
            : 'all';
    }

    /**
     * @param  Collection<int, QuestUnit>  $units
     * @return Collection<int, QuestUnit>
     */
    private function filterUnitsByProgress(Collection $units, string $progressFilter): Collection
    {
        if ($progressFilter === 'all') {
            return $units->values();
        }

        return $units
            ->filter(fn (QuestUnit $unit) => $this->matchesProgressFilter($unit, $progressFilter))
            ->values();
    }

    private function matchesProgressFilter(QuestUnit $unit, string $progressFilter): bool
    {
        $status = $this->resolveUnitProgressStatus($unit);

        return match ($progressFilter) {
            'in_progress' => in_array($status, [
                QuestUnitProgressStatus::IN_PROGRESS,
                QuestUnitProgressStatus::HAS_REJECTED,
            ], true),
            'completed' => $status === QuestUnitProgressStatus::COMPLETED,
            'not_started' => $status === QuestUnitProgressStatus::NOT_STARTED,
            default => true,
        };
    }

    private function resolveUnitProgressStatus(QuestUnit $unit): string
    {
        $questStatuses = $unit->quests->map(function ($quest): array {
            $progress = $quest->relationLoaded('progressRecords')
                ? $quest->progressRecords->first()
                : null;

            return [
                'progressStatus' => QuestProgressStatus::resolveFromProgress($progress),
            ];
        });

        return QuestUnitProgressStatus::resolveFromQuests($questStatuses);
    }

    /**
     * @param  Collection<int, QuestUnit>  $items
     */
    private function paginateCollection(Collection $items, int $page, int $perPage): LengthAwarePaginator
    {
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }
}
