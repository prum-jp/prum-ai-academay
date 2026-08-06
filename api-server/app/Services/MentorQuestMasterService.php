<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Support\QuestDifficulty;
use App\Support\QuestTier;
use Illuminate\Support\Collection;

class MentorQuestMasterService
{
    public const PER_PAGE = 20;

    public function __construct(
        private readonly MentorQuestMasterCsvExporter $csvExporter,
    ) {}

    /**
     * @return array{
     *     units: list<array<string, mixed>>,
     *     teamQuests: list<array<string, mixed>>,
     *     specialQuests: list<array<string, mixed>>
     * }
     */
    public function collectGrouped(?string $kind = null, ?string $search = null): array
    {
        $normalizedSearch = $search !== null ? trim($search) : null;
        $hasSearch = $normalizedSearch !== null && $normalizedSearch !== '';

        $units = $this->shouldIncludeUnits($kind)
            ? $this->collectUnitGroups($hasSearch, $normalizedSearch)
            : collect();

        $teamQuests = $this->shouldIncludeKind($kind, 'team_quest')
            ? $this->collectBoardQuestRows(Quest::TYPE_TEAM, $hasSearch, $normalizedSearch)
            : collect();

        $specialQuests = $this->shouldIncludeKind($kind, 'special_quest')
            ? $this->collectBoardQuestRows(Quest::TYPE_SPECIAL, $hasSearch, $normalizedSearch)
            : collect();

        return [
            'units' => $units->values()->all(),
            'teamQuests' => $teamQuests->values()->all(),
            'specialQuests' => $specialQuests->values()->all(),
        ];
    }

    /**
     * @return array{
     *     units: list<array<string, mixed>>,
     *     teamQuests: list<array<string, mixed>>,
     *     specialQuests: list<array<string, mixed>>,
     *     meta: array<string, int>
     * }
     */
    public function paginateGrouped(?string $kind, ?string $search, int $page): array
    {
        $grouped = $this->collectGrouped($kind, $search);
        $units = collect($grouped['units']);
        $perPage = self::PER_PAGE;
        $total = $units->count();
        $page = max(1, $page);

        return [
            'units' => $units->slice(($page - 1) * $perPage, $perPage)->values()->all(),
            'teamQuests' => $grouped['teamQuests'],
            'specialQuests' => $grouped['specialQuests'],
            'meta' => $this->buildMeta($total, $perPage, $page),
        ];
    }

    public function exportCsv(): string
    {
        $rows = collect();

        $units = QuestUnit::query()
            ->with(['quests' => function ($query): void {
                $query->where('type', Quest::TYPE_PERSONAL)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }, 'quests.tool'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($units as $unit) {
            foreach ($unit->quests as $quest) {
                $rows->push(
                    $this->csvExporter->mapChildQuestRow($this->mapChildQuest($quest, $unit)),
                );
            }
        }

        return $this->csvExporter->export($rows);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectUnitGroups(bool $hasSearch, ?string $search): Collection
    {
        $units = QuestUnit::query()
            ->with(['quests' => function ($query): void {
                $query->where('type', Quest::TYPE_PERSONAL)
                    ->with('tool')
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $units
            ->map(function (QuestUnit $unit) use ($hasSearch, $search): ?array {
                $quests = $unit->quests
                    ->filter(function (Quest $quest) use ($hasSearch, $search, $unit): bool {
                        if (! $hasSearch) {
                            return true;
                        }

                        $haystack = $quest->title.' '.$unit->title;

                        return $this->matchesSearch($haystack, (string) $search);
                    })
                    ->map(fn (Quest $quest): array => $this->mapUnitQuestRow($quest))
                    ->values()
                    ->all();

                if ($hasSearch) {
                    $unitMatches = $this->matchesSearch($unit->title, (string) $search);
                    if (! $unitMatches && $quests === []) {
                        return null;
                    }
                }

                return [
                    'id' => $unit->id,
                    'title' => $unit->title,
                    'sortOrder' => (int) $unit->sort_order,
                    'questCount' => count($quests),
                    'quests' => $quests,
                ];
            })
            ->filter(fn (?array $unit): bool => $unit !== null)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectBoardQuestRows(
        string $type,
        bool $hasSearch,
        ?string $search,
    ): Collection {
        return Quest::query()
            ->where('type', $type)
            ->whereNull('quest_unit_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(function (Quest $quest) use ($hasSearch, $search): bool {
                if (! $hasSearch) {
                    return true;
                }

                return $this->matchesSearch($quest->title, (string) $search);
            })
            ->map(fn (Quest $quest): array => $this->mapQuestRow(
                $quest,
                $type === Quest::TYPE_TEAM ? 'team_quest' : 'special_quest',
            ));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapUnitQuestRow(Quest $quest): array
    {
        return [
            'id' => $quest->id,
            'kind' => 'child_quest',
            'title' => $quest->title,
            'sortOrder' => (int) $quest->sort_order,
            'unitId' => $quest->quest_unit_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapQuestRow(Quest $quest, string $kind): array
    {
        return [
            'id' => $quest->id,
            'kind' => $kind,
            'title' => $quest->title,
            'sortOrder' => (int) $quest->sort_order,
            'unitId' => $quest->quest_unit_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPersonalUnit(QuestUnit $unit): array
    {
        return [
            'title' => $unit->title,
            'description' => $unit->description ?? '',
            'sortOrder' => (int) $unit->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapChildQuest(Quest $quest, QuestUnit $unit): array
    {
        return [
            'title' => $quest->title,
            'unitTitle' => $unit->title,
            'description' => $quest->description ?? '',
            'clearCondition' => $quest->clear_condition ?? '',
            'toolCode' => $this->resolveToolLabel($quest),
            'difficulty' => $quest->difficulty,
            'experiencePoints' => QuestDifficulty::experiencePoints(
                $quest->difficulty !== null ? (int) $quest->difficulty : null,
            ),
            'sortOrder' => (int) $quest->sort_order,
            'unitSortOrder' => (int) $unit->sort_order,
            'questTier' => QuestTier::resolve(
                $quest->quest_tier,
                $quest->unlock_level !== null ? (int) $quest->unlock_level : null,
            ),
            'questTierLabel' => QuestTier::label(
                QuestTier::resolve(
                    $quest->quest_tier,
                    $quest->unlock_level !== null ? (int) $quest->unlock_level : null,
                ),
            ),
        ];
    }

    private function resolveToolLabel(Quest $quest): ?string
    {
        $tool = $quest->tool;
        if ($tool === null) {
            return null;
        }

        return $tool->name !== '' ? $tool->name : $tool->code;
    }

    private function shouldIncludeUnits(?string $kind): bool
    {
        return $kind === null
            || $kind === 'personal_unit'
            || $kind === 'child_quest';
    }

    private function shouldIncludeKind(?string $kind, string $targetKind): bool
    {
        return $kind === null || $kind === $targetKind;
    }

    /**
     * @return array<string, int>
     */
    private function buildMeta(int $total, int $perPage, int $page): array
    {
        $lastPage = max(1, (int) ceil($total / $perPage));

        return [
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'perPage' => $perPage,
            'total' => $total,
        ];
    }

    private function matchesSearch(string $haystack, string $search): bool
    {
        return mb_stripos($haystack, $search) !== false;
    }
}
