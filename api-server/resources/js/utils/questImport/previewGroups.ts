import type { QuestImportItem } from '@/types/questImport';

export interface QuestImportPreviewGroupEntry {
    item: QuestImportItem;
    index: number;
}

export interface QuestImportPreviewGroup {
    key: string;
    entries: QuestImportPreviewGroupEntry[];
}

/** プレビュー一覧をユニット単位のグループに分割 */
export const buildPreviewGroups = (items: QuestImportItem[]): QuestImportPreviewGroup[] => {
    const groups: QuestImportPreviewGroup[] = [];
    let current: QuestImportPreviewGroup | null = null;

    items.forEach((item, index) => {
        if (item.kind === 'personal_unit' || current === null) {
            current = {
                key: item.clientId,
                entries: [{ item, index }],
            };
            groups.push(current);
            return;
        }

        current.entries.push({ item, index });
    });

    return groups;
};
