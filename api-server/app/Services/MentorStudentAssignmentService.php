<?php

namespace App\Services;

use App\Models\Curriculum;
use App\Models\Quest;
use App\Models\QuestUnit;
use App\Models\StudentCurriculumAssignment;
use App\Models\StudentQuestAssignment;
use App\Models\StudentQuestExclusion;
use App\Models\StudentQuestUnitAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MentorStudentAssignmentService
{
    public function __construct(
        private readonly StudentQuestAssignmentQuery $assignmentQuery,
        private readonly StudentNotificationService $studentNotificationService,
    ) {}

    /**
     * @return array{
     *     curricula: list<array<string, mixed>>,
     *     units: list<array<string, mixed>>
     * }
     */
    public function assignmentOptionsForStudent(User $student): array
    {
        $assignedCurriculumIds = $this->assignmentQuery->assignedCurriculumIds($student);
        $directUnitIds = $this->assignmentQuery->directlyAssignedUnitIds($student);
        $effectiveUnitIds = $this->assignmentQuery->effectiveUnitIds($student);

        $curricula = Curriculum::query()
            ->with(['questUnits:id,title'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Curriculum $curriculum) => [
                'id' => $curriculum->id,
                'name' => $curriculum->name,
                'description' => $curriculum->description,
                'unitCount' => $curriculum->questUnits->count(),
                'units' => $curriculum->questUnits->map(fn (QuestUnit $unit) => [
                    'id' => $unit->id,
                    'title' => $unit->title,
                ])->values()->all(),
                'isAssigned' => in_array($curriculum->id, $assignedCurriculumIds, true),
            ])
            ->values()
            ->all();

        $units = QuestUnit::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (QuestUnit $unit) => [
                'id' => $unit->id,
                'title' => $unit->title,
                'isDirectlyAssigned' => in_array($unit->id, $directUnitIds, true),
                'isEffective' => in_array($unit->id, $effectiveUnitIds, true),
            ])
            ->values()
            ->all();

        return [
            'curricula' => $curricula,
            'units' => $units,
        ];
    }

    /**
     * @param  list<int>  $curriculumIds
     * @param  list<int>  $unitIds
     */
    public function syncAssignments(User $student, array $curriculumIds, array $unitIds, User $mentor): void
    {
        $previousCurriculumIds = $student->curriculumAssignments()
            ->pluck('curriculum_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::transaction(function () use ($student, $curriculumIds, $unitIds, $mentor): void {
            $now = now();

            $student->curriculumAssignments()->delete();
            foreach ($curriculumIds as $curriculumId) {
                $student->curriculumAssignments()->create([
                    'curriculum_id' => $curriculumId,
                    'assigned_by' => $mentor->id,
                    'assigned_at' => $now,
                ]);
            }

            $student->questUnitAssignments()->delete();
            foreach ($unitIds as $unitId) {
                $student->questUnitAssignments()->create([
                    'quest_unit_id' => $unitId,
                    'assigned_by' => $mentor->id,
                    'assigned_at' => $now,
                ]);
            }
        });

        $newCurriculumIds = array_diff(
            array_map('intval', $curriculumIds),
            $previousCurriculumIds,
        );

        if ($newCurriculumIds !== []) {
            $curricula = Curriculum::query()
                ->whereIn('id', $newCurriculumIds)
                ->get()
                ->keyBy('id');

            foreach ($newCurriculumIds as $curriculumId) {
                $curriculum = $curricula->get($curriculumId);
                if ($curriculum !== null) {
                    $this->studentNotificationService->notifyCurriculumAdded(
                        $student,
                        $curriculum,
                        $mentor,
                    );
                }
            }
        }
    }

    public function assignUnitToAllStudents(QuestUnit $unit, User $mentor): int
    {
        $studentIds = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return 0;
        }

        $now = now();
        $count = 0;

        DB::transaction(function () use ($studentIds, $unit, $mentor, $now, &$count): void {
            foreach ($studentIds as $studentId) {
                $assignment = StudentQuestUnitAssignment::query()->firstOrCreate(
                    [
                        'user_id' => $studentId,
                        'quest_unit_id' => $unit->id,
                    ],
                    [
                        'assigned_by' => $mentor->id,
                        'assigned_at' => $now,
                    ],
                );

                if ($assignment->wasRecentlyCreated) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function assignCurriculumToAllStudents(Curriculum $curriculum, User $mentor): int
    {
        $studentIds = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return 0;
        }

        $now = now();
        $count = 0;

        DB::transaction(function () use ($studentIds, $curriculum, $mentor, $now, &$count): void {
            foreach ($studentIds as $studentId) {
                $assignment = StudentCurriculumAssignment::query()->firstOrCreate(
                    [
                        'user_id' => $studentId,
                        'curriculum_id' => $curriculum->id,
                    ],
                    [
                        'assigned_by' => $mentor->id,
                        'assigned_at' => $now,
                    ],
                );

                if ($assignment->wasRecentlyCreated) {
                    $count++;
                    $student = User::query()->find($studentId);
                    if ($student !== null) {
                        $this->studentNotificationService->notifyCurriculumAdded(
                            $student,
                            $curriculum,
                            $mentor,
                        );
                    }
                }
            }
        });

        return $count;
    }

    /**
     * @return array{
     *     userId: int,
     *     quests: list<array{
     *         questUnitId: int,
     *         name: string,
     *         assigned: bool,
     *         directlyAssigned: bool,
     *         canUnassign: bool,
     *         viaCurriculum: bool,
     *         childQuests: list<array{
     *             id: int,
     *             title: string,
     *             assigned: bool,
     *             directlyAssigned: bool,
     *             viaCurriculum: bool,
     *             canUnassign: bool
     *         }>
     *     }>
     * }
     */
    public function questUnitAssignmentStatusForStudent(User $student): array
    {
        $effectiveUnitIds = $this->assignmentQuery->effectiveUnitIds($student);
        $directUnitIds = $this->assignmentQuery->directlyAssignedUnitIds($student);
        $directQuestIds = $this->assignmentQuery->directlyAssignedQuestIds($student);
        $excludedQuestIds = $this->assignmentQuery->excludedQuestIds($student);

        $quests = QuestUnit::query()
            ->with(['quests' => fn ($query) => $query
                ->where('type', Quest::TYPE_PERSONAL)
                ->orderBy('sort_order')
                ->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (QuestUnit $unit) => [
                'questUnitId' => $unit->id,
                'name' => $unit->title,
                'assigned' => in_array($unit->id, $effectiveUnitIds, true),
                'directlyAssigned' => in_array($unit->id, $directUnitIds, true),
                'canUnassign' => in_array($unit->id, $directUnitIds, true),
                'viaCurriculum' => in_array($unit->id, $effectiveUnitIds, true)
                    && ! in_array($unit->id, $directUnitIds, true),
                'childQuests' => $unit->quests
                    ->map(function (Quest $quest) use (
                        $student,
                        $effectiveUnitIds,
                        $directUnitIds,
                        $directQuestIds,
                        $excludedQuestIds,
                    ) {
                        $status = $this->assignmentQuery->childQuestAssignmentStatus(
                            $student,
                            $quest,
                            $effectiveUnitIds,
                            $directUnitIds,
                            $directQuestIds,
                            $excludedQuestIds,
                        );

                        return [
                            'id' => $quest->id,
                            'title' => $quest->title,
                            ...$status,
                        ];
                    })
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return [
            'userId' => $student->id,
            'quests' => $quests,
        ];
    }

    public function assignUnitToStudent(User $student, QuestUnit $unit, User $mentor): void
    {
        StudentQuestUnitAssignment::query()->firstOrCreate(
            [
                'user_id' => $student->id,
                'quest_unit_id' => $unit->id,
            ],
            [
                'assigned_by' => $mentor->id,
                'assigned_at' => now(),
            ],
        );

        $this->clearQuestExclusionsForUnit($student, $unit);
    }

    public function unassignUnitFromStudent(User $student, QuestUnit $unit): bool
    {
        $removed = StudentQuestUnitAssignment::query()
            ->where('user_id', $student->id)
            ->where('quest_unit_id', $unit->id)
            ->delete() > 0;

        if ($removed) {
            $this->clearQuestExclusionsForUnit($student, $unit);
        }

        return $removed;
    }

    public function assignQuestToStudent(User $student, Quest $quest, User $mentor): void
    {
        StudentQuestAssignment::query()->firstOrCreate(
            [
                'user_id' => $student->id,
                'quest_id' => $quest->id,
            ],
            [
                'assigned_by' => $mentor->id,
                'assigned_at' => now(),
            ],
        );

        StudentQuestExclusion::query()
            ->where('user_id', $student->id)
            ->where('quest_id', $quest->id)
            ->delete();
    }

    public function unassignQuestFromStudent(User $student, Quest $quest): bool
    {
        $directRemoved = StudentQuestAssignment::query()
            ->where('user_id', $student->id)
            ->where('quest_id', $quest->id)
            ->delete() > 0;

        if ($directRemoved) {
            return true;
        }

        $unitId = $quest->quest_unit_id;

        if ($unitId === null) {
            return false;
        }

        $isDirectUnitAssigned = in_array(
            $unitId,
            $this->assignmentQuery->directlyAssignedUnitIds($student),
            true,
        );

        if (! $isDirectUnitAssigned) {
            return false;
        }

        StudentQuestExclusion::query()->firstOrCreate([
            'user_id' => $student->id,
            'quest_id' => $quest->id,
        ]);

        return true;
    }

    private function clearQuestExclusionsForUnit(User $student, QuestUnit $unit): void
    {
        $questIds = $unit->quests()
            ->where('type', Quest::TYPE_PERSONAL)
            ->pluck('id');

        if ($questIds->isEmpty()) {
            return;
        }

        StudentQuestExclusion::query()
            ->where('user_id', $student->id)
            ->whereIn('quest_id', $questIds)
            ->delete();
    }

    /**
     * @param  list<int>  $studentIds
     */
    public function syncCurriculumAssignments(
        Curriculum $curriculum,
        string $assignmentTarget,
        array $studentIds,
        User $mentor,
    ): void {
        DB::transaction(function () use ($curriculum, $assignmentTarget, $studentIds, $mentor): void {
            $targetIds = $assignmentTarget === 'all'
                ? User::query()
                    ->where('role', User::ROLE_STUDENT)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
                : array_values(array_unique(array_map('intval', $studentIds)));

            StudentCurriculumAssignment::query()
                ->where('curriculum_id', $curriculum->id)
                ->whereNotIn('user_id', $targetIds)
                ->delete();

            $now = now();

            foreach ($targetIds as $studentId) {
                $assignment = StudentCurriculumAssignment::query()->firstOrCreate(
                    [
                        'user_id' => $studentId,
                        'curriculum_id' => $curriculum->id,
                    ],
                    [
                        'assigned_by' => $mentor->id,
                        'assigned_at' => $now,
                    ],
                );

                if ($assignment->wasRecentlyCreated) {
                    $student = User::query()->find($studentId);
                    if ($student !== null) {
                        $this->studentNotificationService->notifyCurriculumAdded(
                            $student,
                            $curriculum,
                            $mentor,
                        );
                    }
                }
            }
        });
    }

    /**
     * @return array{assignmentTarget: string, assignedStudentIds: list<int>}
     */
    public function curriculumAssignmentState(Curriculum $curriculum): array
    {
        $assignedStudentIds = StudentCurriculumAssignment::query()
            ->where('curriculum_id', $curriculum->id)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $totalStudents = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->count();

        if ($totalStudents > 0 && count($assignedStudentIds) === $totalStudents) {
            return [
                'assignmentTarget' => 'all',
                'assignedStudentIds' => $assignedStudentIds,
            ];
        }

        return [
            'assignmentTarget' => 'selected',
            'assignedStudentIds' => $assignedStudentIds,
        ];
    }
}
