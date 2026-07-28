import type { QuestImportItem } from '@/types/questImport';

export const applyPublishChange = (
    item: QuestImportItem,
    isPublished: boolean,
): QuestImportItem => ({
    ...item,
    isPublished,
    action:
        item.action === 'unchanged' && item.isPublished !== isPublished ? 'update' : item.action,
});

export const setAllItemsPublished = (
    items: QuestImportItem[],
    isPublished: boolean,
): QuestImportItem[] => items.map((item) => applyPublishChange(item, isPublished));

export const syncUnitPublishToItems = (
    items: QuestImportItem[],
    unitTitle: string,
    isPublished: boolean,
): QuestImportItem[] =>
    items.map((item) => {
        const isTargetUnit = item.kind === 'personal_unit' && item.title === unitTitle;
        const isChildOfUnit = item.kind === 'child_quest' && item.unitTitle === unitTitle;

        if (!isTargetUnit && !isChildOfUnit) {
            return item;
        }

        return applyPublishChange(item, isPublished);
    });
