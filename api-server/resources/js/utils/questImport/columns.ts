/** CSV ヘッダー別名 → 論理列キー */
export const QUEST_IMPORT_COLUMN_ALIASES: Record<string, string[]> = {
    csvNo: ['no'],
    unitSortOrder: ['unit'],
    questNo: ['quest'],
    todoNote: ['to do', 'todo'],
    unitTitle: ['unit名', 'unit_name'],
    title: ['quest名', 'quest_name'],
    description: ['内容', '概要', 'description'],
    unitDescription: ['unit概要', 'ユニット概要', 'unit_description', 'ユニット内容'],
    purpose: ['目的', 'purpose'],
    clearCondition: ['完了条件', 'クリア条件', 'clear_condition'],
    toolName: ['ツール', 'tool'],
    difficulty: ['難度', 'difficulty'],
    estimatedDuration: ['所要時間', 'duration'],
};

export const normalizeCsvHeader = (header: string): string => header.trim().toLowerCase();

export const buildCsvHeaderIndex = (headerRow: string[]): Map<string, number> => {
    const normalizedToIndex = new Map<string, number>();

    headerRow.forEach((header, index) => {
        normalizedToIndex.set(normalizeCsvHeader(header), index);
    });

    const logicalIndex = new Map<string, number>();

    for (const [logicalKey, aliases] of Object.entries(QUEST_IMPORT_COLUMN_ALIASES)) {
        for (const alias of aliases) {
            const index = normalizedToIndex.get(alias);
            if (index !== undefined) {
                logicalIndex.set(logicalKey, index);
                break;
            }
        }
    }

    return logicalIndex;
};

export const getCsvCell = (
    cells: string[],
    headerIndex: Map<string, number>,
    column: string,
): string => {
    const columnIndex = headerIndex.get(column);
    if (columnIndex === undefined) {
        return '';
    }

    return cells[columnIndex] ?? '';
};
