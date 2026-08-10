<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentQuestAccessService
{
    public function __construct(
        private readonly StudentQuestAssignmentQuery $assignmentQuery,
    ) {}

    public function findUnitForStudent(User $student, int $unitId): QuestUnit
    {
        $unit = $this->assignmentQuery
            ->assignedUnits($student)
            ->with([
                'rewards',
                'quests' => function ($query) use ($student): void {
                    $assignedQuestIds = $this->assignmentQuery
                        ->assignedPersonalQuests($student)
                        ->pluck('id');

                    $query
                        ->where('type', Quest::TYPE_PERSONAL)
                        ->when(
                            $assignedQuestIds->isNotEmpty(),
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
            ->whereKey($unitId)
            ->first();

        if ($unit === null) {
            throw (new ModelNotFoundException())->setModel(QuestUnit::class, [$unitId]);
        }

        return $unit;
    }

    public function findQuestForStudent(User $student, int $questId): Quest
    {
        $quest = Quest::query()->whereKey($questId)->first();

        if ($quest === null) {
            throw (new ModelNotFoundException())->setModel(Quest::class, [$questId]);
        }

        if ($quest->type === Quest::TYPE_PERSONAL) {
            $isAccessible = $this->assignmentQuery
                ->assignedPersonalQuests($student)
                ->whereKey($questId)
                ->exists();

            if (! $isAccessible) {
                throw (new ModelNotFoundException())->setModel(Quest::class, [$questId]);
            }
        } elseif (! $quest->is_published || $quest->quest_unit_id !== null) {
            throw (new ModelNotFoundException())->setModel(Quest::class, [$questId]);
        }

        return $quest->load([
            'rewards',
            'tool',
            'tools',
            'questUnit:id,title,sort_order',
            'progressRecords' => function ($query) use ($student): void {
                $query->where('user_id', $student->id);
            },
        ])->loadCount([
            'applications as applications_count' => function ($query): void {
                $query->whereIn('status', ['applied', 'approved']);
            },
        ]);
    }
}
