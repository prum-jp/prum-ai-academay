<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class StudentQuestAssignmentQuery
{
    /**
     * @return Builder<QuestUnit>
     */
    public function assignedUnits(User $student): Builder
    {
        return QuestUnit::query()
            ->where(function (Builder $query) use ($student): void {
                $query
                    ->whereHas('studentAssignments', function (Builder $assignmentQuery) use ($student): void {
                        $assignmentQuery->where('user_id', $student->id);
                    })
                    ->orWhereHas('curricula.studentAssignments', function (Builder $assignmentQuery) use ($student): void {
                        $assignmentQuery->where('user_id', $student->id);
                    })
                    ->orWhereHas('quests', function (Builder $questQuery) use ($student): void {
                        $this->applyDirectlyAssignedQuestConstraint($questQuery, $student);
                    });
            });
    }

    /**
     * @return Builder<Quest>
     */
    public function assignedPersonalQuests(User $student): Builder
    {
        return Quest::query()
            ->where('type', Quest::TYPE_PERSONAL)
            ->where(function (Builder $query) use ($student): void {
                $query
                    ->where(function (Builder $directQuery) use ($student): void {
                        $this->applyDirectlyAssignedQuestConstraint($directQuery, $student);
                    })
                    ->orWhere(function (Builder $viaUnitQuery) use ($student): void {
                        $viaUnitQuery
                            ->whereHas('questUnit', function (Builder $unitQuery) use ($student): void {
                                $this->applyEffectiveUnitConstraint($unitQuery, $student);
                            })
                            ->whereDoesntHave('exclusions', function (Builder $exclusionQuery) use ($student): void {
                                $exclusionQuery->where('user_id', $student->id);
                            })
                            ->whereDoesntHave('studentAssignments', function (Builder $assignmentQuery) use ($student): void {
                                $assignmentQuery->where('user_id', $student->id);
                            });
                    });
            });
    }

    /**
     * @return list<int>
     */
    public function effectiveUnitIds(User $student): array
    {
        return QuestUnit::query()
            ->where(function (Builder $query) use ($student): void {
                $this->applyEffectiveUnitConstraint($query, $student);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    public function directlyAssignedUnitIds(User $student): array
    {
        return QuestUnit::query()
            ->whereHas('studentAssignments', function (Builder $query) use ($student): void {
                $query->where('user_id', $student->id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    public function directlyAssignedQuestIds(User $student): array
    {
        return Quest::query()
            ->where('type', Quest::TYPE_PERSONAL)
            ->whereHas('studentAssignments', function (Builder $query) use ($student): void {
                $query->where('user_id', $student->id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    public function excludedQuestIds(User $student): array
    {
        return $student->questExclusions()
            ->pluck('quest_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    public function assignedCurriculumIds(User $student): array
    {
        return $student->curriculumAssignments()
            ->pluck('curriculum_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array{
     *     assigned: bool,
     *     directlyAssigned: bool,
     *     viaCurriculum: bool,
     *     canUnassign: bool
     * }
     */
    public function childQuestAssignmentStatus(
        User $student,
        Quest $quest,
        array $effectiveUnitIds,
        array $directUnitIds,
        array $directQuestIds,
        array $excludedQuestIds,
    ): array {
        $unitId = $quest->quest_unit_id;
        $directlyAssigned = in_array($quest->id, $directQuestIds, true);
        $excluded = in_array($quest->id, $excludedQuestIds, true);
        $unitEffective = $unitId !== null && in_array($unitId, $effectiveUnitIds, true);
        $unitDirect = $unitId !== null && in_array($unitId, $directUnitIds, true);
        $assigned = $directlyAssigned || ($unitEffective && ! $excluded);
        $viaCurriculum = $assigned && ! $directlyAssigned && $unitEffective && ! $unitDirect;

        return [
            'assigned' => $assigned,
            'directlyAssigned' => $directlyAssigned,
            'viaCurriculum' => $viaCurriculum,
            'canUnassign' => $directlyAssigned || ($unitDirect && ! $excluded),
        ];
    }

    /**
     * @param  Builder<QuestUnit>|Builder<Quest>  $query
     */
    private function applyEffectiveUnitConstraint(Builder $query, User $student): void
    {
        $query
            ->whereHas('studentAssignments', function (Builder $assignmentQuery) use ($student): void {
                $assignmentQuery->where('user_id', $student->id);
            })
            ->orWhereHas('curricula.studentAssignments', function (Builder $assignmentQuery) use ($student): void {
                $assignmentQuery->where('user_id', $student->id);
            });
    }

    /**
     * @param  Builder<Quest>  $query
     */
    private function applyDirectlyAssignedQuestConstraint(Builder $query, User $student): void
    {
        $query->whereHas('studentAssignments', function (Builder $assignmentQuery) use ($student): void {
            $assignmentQuery->where('user_id', $student->id);
        });
    }
}
