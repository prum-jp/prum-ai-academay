import type { QuestItem, QuestUnitItem } from '@/types/quest';
import type { QuestProgressStatus } from '@/constants/questProgress';
import type { UnitProgressStatus } from '@/constants/unitProgress';
import { resolveUnitProgressStatus } from '@/constants/unitProgress';

export const recalculateUnitProgress = (unit: QuestUnitItem): QuestUnitItem => {
    const completedCount = unit.quests.filter((quest) => quest.isCompleted).length;
    const totalCount = unit.quests.length;

    return {
        ...unit,
        completedCount,
        totalCount,
        isCompleted: totalCount > 0 && completedCount === totalCount,
        progressStatus: resolveUnitProgressStatus(unit.quests),
    };
};

export const isUnitIndeterminate = (unit: QuestUnitItem): boolean =>
    unit.completedCount > 0 && !unit.isCompleted;

export const updateUnitQuest = (unit: QuestUnitItem, updated: QuestItem): QuestUnitItem =>
    recalculateUnitProgress({
        ...unit,
        quests: unit.quests.map((quest) => (quest.id === updated.id ? updated : quest)),
    });

export const getQuestsToUpdateForUnit = (
    unit: QuestUnitItem,
): { targetStatus: QuestProgressStatus; quests: QuestItem[] } => {
    const targetStatus: QuestProgressStatus = unit.isCompleted ? 'not_started' : 'completed';

    return {
        targetStatus,
        quests: unit.quests.filter(
            (quest) =>
                !quest.isLocked &&
                (targetStatus === 'completed'
                    ? quest.progressStatus !== 'completed'
                    : quest.progressStatus !== 'not_started'),
        ),
    };
};

export const unitContainsQuest = (unit: QuestUnitItem, questId: number): boolean =>
    unit.quests.some((quest) => quest.id === questId);
