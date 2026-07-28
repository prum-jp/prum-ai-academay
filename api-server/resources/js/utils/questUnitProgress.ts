import type { QuestItem, QuestUnitItem } from '@/types/quest';

export const recalculateUnitProgress = (unit: QuestUnitItem): QuestUnitItem => {
    const completedCount = unit.quests.filter((quest) => quest.isCompleted).length;
    const totalCount = unit.quests.length;

    return {
        ...unit,
        completedCount,
        totalCount,
        isCompleted: totalCount > 0 && completedCount === totalCount,
    };
};

export const isUnitIndeterminate = (unit: QuestUnitItem): boolean =>
    unit.completedCount > 0 && !unit.isCompleted;

export const updateUnitQuest = (unit: QuestUnitItem, updated: QuestItem): QuestUnitItem =>
    recalculateUnitProgress({
        ...unit,
        quests: unit.quests.map((quest) => (quest.id === updated.id ? updated : quest)),
    });

export const getQuestsToToggleForUnit = (
    unit: QuestUnitItem,
): { targetCompleted: boolean; quests: QuestItem[] } => {
    const targetCompleted = !unit.isCompleted;

    return {
        targetCompleted,
        quests: unit.quests.filter(
            (quest) => !quest.isLocked && quest.isCompleted !== targetCompleted,
        ),
    };
};

export const unitContainsQuest = (unit: QuestUnitItem, questId: number): boolean =>
    unit.quests.some((quest) => quest.id === questId);
