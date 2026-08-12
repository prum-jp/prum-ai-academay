import type { QuestImportItem, QuestImportMeta } from '@/types/mentor-quest/questImport';

const resolveAction = (item: QuestImportItem): string => item.action ?? 'create';

export const computeQuestImportMeta = (items: QuestImportItem[]): QuestImportMeta => ({
    total: items.length,
    createCount: items.filter((item) => resolveAction(item) === 'create').length,
    updateCount: items.filter((item) => resolveAction(item) === 'update').length,
    unchangedCount: items.filter((item) => resolveAction(item) === 'unchanged').length,
    errorCount: items.filter((item) => (item.errors?.length ?? 0) > 0).length,
});

export const refreshImportMetaCounts = (
    items: QuestImportItem[],
    meta: QuestImportMeta | null,
): QuestImportMeta | null => {
    if (meta === null) {
        return null;
    }

    const computed = computeQuestImportMeta(items);

    return {
        ...meta,
        total: computed.total,
        createCount: computed.createCount,
        updateCount: computed.updateCount,
        unchangedCount: computed.unchangedCount,
        errorCount: computed.errorCount,
    };
};
