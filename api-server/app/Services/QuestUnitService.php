<?php

namespace App\Services;

use App\Models\QuestUnit;
use App\Models\User;
use App\Support\QuestProgressStatus;
use App\Support\QuestUnitProgressStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

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
        $page = max(1, $page);
        $normalizedFilter = $this->normalizeProgressFilter($progressFilter);

        if ($normalizedFilter === 'all') {
            return $this->unitQuery($student)->paginate(self::PER_PAGE, ['*'], 'page', $page);
        }

        return $this->paginateFilteredUnits($student, $page, $normalizedFilter);
    }

    private function paginateFilteredUnits(User $student, int $page, string $progressFilter): LengthAwarePaginator
    {
        $assignedQuestIds = $this->assignedQuestIdsFor($student);

        $units = $this->assignmentQuery
            ->assignedUnits($student)
            ->with([
                'quests' => function ($query) use ($student, $assignedQuestIds): void {
                    $query
                        ->where('type', 'personal')
                        ->when(
                            $assignedQuestIds !== [],
                            fn ($questQuery) => $questQuery->whereIn('id', $assignedQuestIds),
                            fn ($questQuery) => $questQuery->whereRaw('0 = 1'),
                        )
                        ->with([
                            'progressRecords' => function ($progressQuery) use ($student): void {
                                $progressQuery->where('user_id', $student->id);
                            },
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $filteredIds = $units
            ->filter(fn (QuestUnit $unit) => $this->matchesProgressFilter($unit, $progressFilter))
            ->pluck('id')
            ->values();

        $total = $filteredIds->count();
        $pageIds = $filteredIds
            ->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)
            ->values();

        if ($pageIds->isEmpty()) {
            return $this->emptyPaginator($total, $page);
        }

        $items = $this->unitQuery($student)
            ->whereIn('id', $pageIds->all())
            ->get()
            ->sortBy(fn (QuestUnit $unit) => $pageIds->search($unit->id))
            ->values();

        return new Paginator(
            $items,
            $total,
            self::PER_PAGE,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    /**
     * @return list<int>
     */
    private function assignedQuestIdsFor(User $student): array
    {
        return $this->assignmentQuery
            ->assignedPersonalQuests($student)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return Builder<QuestUnit>
     */
    private function unitQuery(User $student): Builder
    {
        $assignedQuestIds = $this->assignedQuestIdsFor($student);

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
                            'tools',
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

    private function emptyPaginator(int $total, int $page): Paginator
    {
        return new Paginator(
            collect(),
            $total,
            self::PER_PAGE,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }
}
