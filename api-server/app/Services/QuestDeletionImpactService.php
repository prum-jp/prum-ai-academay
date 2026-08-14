<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\QuestUnit;
use App\Models\StudentQuestProgress;

class QuestDeletionImpactService
{
    public function __construct(
        private readonly StudentQuestAssignmentQuery $assignmentQuery,
    ) {}

    /**
     * @return array{linkedUserCount: int, hasSubmissions: bool}
     */
    public function forQuest(Quest $quest): array
    {
        $linkedStudentIds = match (true) {
            $quest->type === Quest::TYPE_PERSONAL && $quest->quest_unit_id !== null => $this->assignmentQuery
                ->linkedStudentIdsForChildQuest($quest),
            in_array($quest->type, [Quest::TYPE_TEAM, Quest::TYPE_SPECIAL], true) => $this->assignmentQuery
                ->linkedStudentIdsForBoardQuest($quest),
            default => [],
        };

        return [
            'linkedUserCount' => count($linkedStudentIds),
            'hasSubmissions' => $this->questHasSubmissions($quest->id),
        ];
    }

    /**
     * @return array{linkedUserCount: int, hasSubmissions: bool, childQuestCount: int}
     */
    public function forUnit(QuestUnit $unit): array
    {
        $childQuestIds = $unit->quests()->pluck('id');

        return [
            'linkedUserCount' => count($this->assignmentQuery->linkedStudentIdsForUnit((int) $unit->id)),
            'hasSubmissions' => $childQuestIds->isNotEmpty()
                && StudentQuestProgress::query()
                    ->whereIn('quest_id', $childQuestIds)
                    ->where(function ($query): void {
                        $query->where(function ($inner): void {
                            $inner->whereNotNull('submission_url')
                                ->where('submission_url', '!=', '');
                        })->orWhere(function ($inner): void {
                            $inner->whereNotNull('submission_text')
                                ->where('submission_text', '!=', '');
                        });
                    })
                    ->exists(),
            'childQuestCount' => $childQuestIds->count(),
        ];
    }

    private function questHasSubmissions(int $questId): bool
    {
        return StudentQuestProgress::query()
            ->where('quest_id', $questId)
            ->where(function ($query): void {
                $query->where(function ($inner): void {
                    $inner->whereNotNull('submission_url')
                        ->where('submission_url', '!=', '');
                })->orWhere(function ($inner): void {
                    $inner->whereNotNull('submission_text')
                        ->where('submission_text', '!=', '');
                });
            })
            ->exists();
    }
}
