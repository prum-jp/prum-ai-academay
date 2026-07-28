import type { QuestImportItem } from '@/types/questImport';

export const compareUnitSort = (left: QuestImportItem, right: QuestImportItem): number => {
    const leftOrder = left.sortOrder ?? left.unitSortOrder ?? Number.MAX_SAFE_INTEGER;
    const rightOrder = right.sortOrder ?? right.unitSortOrder ?? Number.MAX_SAFE_INTEGER;

    if (leftOrder !== rightOrder) {
        return leftOrder - rightOrder;
    }

    return left.title.localeCompare(right.title, 'ja');
};

export const compareQuestSort = (left: QuestImportItem, right: QuestImportItem): number => {
    const leftOrder = left.sortOrder ?? Number.MAX_SAFE_INTEGER;
    const rightOrder = right.sortOrder ?? Number.MAX_SAFE_INTEGER;

    if (leftOrder !== rightOrder) {
        return leftOrder - rightOrder;
    }

    const byCsvNo = (left.csvNo ?? '').localeCompare(right.csvNo ?? '', 'ja', { numeric: true });
    if (byCsvNo !== 0) {
        return byCsvNo;
    }

    return left.title.localeCompare(right.title, 'ja');
};

export const sortChildQuests = (quests: QuestImportItem[]): QuestImportItem[] =>
    [...quests].sort(compareQuestSort);
