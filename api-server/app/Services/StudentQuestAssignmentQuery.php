<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestApplication;
use App\Models\QuestUnit;
use App\Models\StudentCurriculumAssignment;
use App\Models\StudentQuestAssignment;
use App\Models\StudentQuestProgress;
use App\Models\StudentQuestUnitAssignment;
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
     * ユニットに実際に割り当たっている受講生 ID（直接割当 + カリキュラム経由）。
     *
     * @return list<int>
     */
    public function linkedStudentIdsForUnit(int $unitId): array
    {
        $directIds = StudentQuestUnitAssignment::query()
            ->where('quest_unit_id', $unitId)
            ->whereHas('student', fn (Builder $query): Builder => $this->applyStudentRoleConstraint($query))
            ->pluck('user_id');

        $viaCurriculumIds = StudentCurriculumAssignment::query()
            ->whereHas('student', fn (Builder $query): Builder => $this->applyStudentRoleConstraint($query))
            ->whereHas('curriculum.questUnits', function (Builder $query) use ($unitId): void {
                $query->where('quest_units.id', $unitId);
            })
            ->pluck('user_id');

        $directChildQuestIds = StudentQuestAssignment::query()
            ->whereHas('quest', function (Builder $query) use ($unitId): void {
                $query
                    ->where('type', Quest::TYPE_PERSONAL)
                    ->where('quest_unit_id', $unitId);
            })
            ->whereHas('student', fn (Builder $query): Builder => $this->applyStudentRoleConstraint($query))
            ->pluck('user_id');

        return $this->normalizeStudentIds(
            $directIds->merge($viaCurriculumIds)->merge($directChildQuestIds),
        );
    }

    /**
     * 子クエストに実際に割り当たっている受講生 ID（直接割当 + ユニット経由、除外を考慮）。
     *
     * @return list<int>
     */
    public function linkedStudentIdsForChildQuest(Quest $quest): array
    {
        if ($quest->type !== Quest::TYPE_PERSONAL || $quest->quest_unit_id === null) {
            return [];
        }

        $unitId = (int) $quest->quest_unit_id;
        $questId = (int) $quest->id;

        $directIds = StudentQuestAssignment::query()
            ->where('quest_id', $questId)
            ->whereHas('student', fn (Builder $query): Builder => $this->applyStudentRoleConstraint($query))
            ->pluck('user_id');

        $viaUnitIds = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->where(function (Builder $query) use ($unitId): void {
                $query
                    ->whereHas('questUnitAssignments', function (Builder $assignmentQuery) use ($unitId): void {
                        $assignmentQuery->where('quest_unit_id', $unitId);
                    })
                    ->orWhereHas('curriculumAssignments.curriculum.questUnits', function (Builder $unitQuery) use ($unitId): void {
                        $unitQuery->where('quest_units.id', $unitId);
                    });
            })
            ->whereDoesntHave('questExclusions', function (Builder $exclusionQuery) use ($questId): void {
                $exclusionQuery->where('quest_id', $questId);
            })
            ->pluck('id');

        return $this->normalizeStudentIds($directIds->merge($viaUnitIds));
    }

    /**
     * チーム / スペシャルクエストの参加受講生 ID（応募済み・承認済み + 進捗あり）。
     *
     * @return list<int>
     */
    public function linkedStudentIdsForBoardQuest(Quest $quest): array
    {
        $applicationIds = QuestApplication::query()
            ->where('quest_id', $quest->id)
            ->whereIn('status', [QuestApplication::STATUS_APPLIED, QuestApplication::STATUS_APPROVED])
            ->whereHas('user', fn (Builder $query): Builder => $this->applyStudentRoleConstraint($query))
            ->pluck('user_id');

        $progressIds = StudentQuestProgress::query()
            ->where('quest_id', $quest->id)
            ->whereHas('user', fn (Builder $query): Builder => $this->applyStudentRoleConstraint($query))
            ->pluck('user_id');

        return $this->normalizeStudentIds($applicationIds->merge($progressIds));
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

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private function applyStudentRoleConstraint(Builder $query): Builder
    {
        return $query->where('role', User::ROLE_STUDENT);
    }

    /**
     * @param  iterable<int|string|null>  $ids
     * @return list<int>
     */
    private function normalizeStudentIds(iterable $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            if ($id === null || $id === '') {
                continue;
            }

            $normalized[(int) $id] = (int) $id;
        }

        return array_values($normalized);
    }
}
