import type { QuestImportItem } from '@/types/questImport';
import {
    buildCsvHeaderIndex,
    getCsvCell,
    QUEST_IMPORT_COLUMN_ALIASES,
} from '@/utils/questImport/columns';
import { parseCsvText } from '@/utils/questImport/csvParser';
import { groupImportItemsByUnit } from '@/utils/questImport/groupItems';

/**
 * CSV列 → quests テーブル対応
 * - 内容 / 概要 → description（【提出物】以降は clear_condition に分割可）
 * - 目的 → description 末尾に 【目的】 として追記（DBに独立列なし）
 * - 完了条件 → clear_condition
 * - 所要時間 → estimated_duration
 * - sort_order:
 *   - ユニット = Unit（なければそのユニット内の最小 No）
 *   - クエスト = No（なければ Quest）
 */
const REQUIRED_COLUMNS: Array<keyof typeof QUEST_IMPORT_COLUMN_ALIASES> = ['unitTitle', 'title'];

const createClientId = (lineNumber: number, suffix = ''): string =>
    `import-${lineNumber}${suffix}-${Math.random().toString(36).slice(2, 8)}`;

const parseIntegerColumn = (
    raw: string,
    lineNumber: number,
    columnLabel: string,
    errors: string[],
): number | undefined => {
    const trimmed = raw.trim();
    if (trimmed === '') {
        return undefined;
    }

    if (!/^\d+$/.test(trimmed)) {
        errors.push(`${lineNumber}行目: ${columnLabel} は整数で入力してください（値: "${trimmed}"）。`);
        return undefined;
    }

    const value = Number(trimmed);
    if (!Number.isInteger(value) || value < 0) {
        errors.push(`${lineNumber}行目: ${columnLabel} が不正です（値: "${trimmed}"）。`);
        return undefined;
    }

    return value;
};

const pickFirstToolName = (raw: string): string => raw.split(/[,、/／]/)[0]?.trim() ?? '';

const splitContentBody = (
    content: string,
): { descriptionBody: string; extractedClearCondition: string } => {
    const marker = '【提出物】';
    const index = content.indexOf(marker);

    if (index === -1) {
        return { descriptionBody: content.trim(), extractedClearCondition: '' };
    }

    return {
        descriptionBody: content.slice(0, index).trim(),
        extractedClearCondition: content.slice(index + marker.length).trim(),
    };
};

const buildDescription = (body: string, purpose: string): string => {
    const parts: string[] = [];

    if (body.trim() !== '') {
        parts.push(body.trim());
    }

    if (purpose.trim() !== '') {
        parts.push(`【目的】\n${purpose.trim()}`);
    }

    return parts.join('\n\n');
};

interface ParsedCsvRow {
    lineNumber: number;
    csvNo: string;
    csvNoValue?: number;
    unitSortOrder?: number;
    questNo?: number;
    todoNote: string;
    unitTitle: string;
    title: string;
    description: string;
    descriptionBody: string;
    purpose: string;
    unitDescription: string;
    clearCondition: string;
    toolName: string;
    difficulty: string;
    estimatedDuration: string;
}

interface UnitEntry {
    unitTitle: string;
    unitSortOrder?: number;
    minCsvNo?: number;
    description?: string;
    descriptionSourceNo?: number;
    lineNumber: number;
}

const pickUnitDescription = (row: {
    unitDescription: string;
    purpose: string;
    descriptionBody: string;
}): string => {
    if (row.unitDescription.trim() !== '') {
        return row.unitDescription.trim();
    }

    if (row.descriptionBody.trim() !== '') {
        return row.descriptionBody.trim();
    }

    return row.purpose.trim();
};

const upsertUnitEntry = (
    unitEntries: Map<string, UnitEntry>,
    row: {
        unitTitle: string;
        unitSortOrder?: number;
        csvNoValue?: number;
        lineNumber: number;
        unitDescription: string;
        purpose: string;
        descriptionBody: string;
    },
): void => {
    const key = row.unitTitle;
    const existing = unitEntries.get(key);
    const candidateDescription = pickUnitDescription(row);
    const candidateNo = row.csvNoValue ?? Number.MAX_SAFE_INTEGER;

    if (!existing) {
        unitEntries.set(key, {
            unitTitle: row.unitTitle,
            unitSortOrder: row.unitSortOrder,
            minCsvNo: row.csvNoValue,
            description: candidateDescription || undefined,
            descriptionSourceNo: candidateDescription ? candidateNo : undefined,
            lineNumber: row.lineNumber,
        });
        return;
    }

    if (
        row.unitSortOrder !== undefined &&
        (existing.unitSortOrder === undefined || row.unitSortOrder < existing.unitSortOrder)
    ) {
        existing.unitSortOrder = row.unitSortOrder;
    }

    if (
        row.csvNoValue !== undefined &&
        (existing.minCsvNo === undefined || row.csvNoValue < existing.minCsvNo)
    ) {
        existing.minCsvNo = row.csvNoValue;
    }

    if (
        candidateDescription !== '' &&
        (existing.description === undefined ||
            existing.description === '' ||
            candidateNo < (existing.descriptionSourceNo ?? Number.MAX_SAFE_INTEGER))
    ) {
        existing.description = candidateDescription;
        existing.descriptionSourceNo = candidateNo;
    }
};

const sortUnitEntries = (left: UnitEntry, right: UnitEntry): number => {
    const leftOrder = left.unitSortOrder ?? left.minCsvNo ?? Number.MAX_SAFE_INTEGER;
    const rightOrder = right.unitSortOrder ?? right.minCsvNo ?? Number.MAX_SAFE_INTEGER;

    if (leftOrder !== rightOrder) {
        return leftOrder - rightOrder;
    }

    const leftNo = left.minCsvNo ?? Number.MAX_SAFE_INTEGER;
    const rightNo = right.minCsvNo ?? Number.MAX_SAFE_INTEGER;
    if (leftNo !== rightNo) {
        return leftNo - rightNo;
    }

    return left.unitTitle.localeCompare(right.unitTitle, 'ja');
};

const sortParsedRows = (left: ParsedCsvRow, right: ParsedCsvRow): number => {
    const leftNo = left.csvNoValue ?? Number.MAX_SAFE_INTEGER;
    const rightNo = right.csvNoValue ?? Number.MAX_SAFE_INTEGER;

    if (leftNo !== rightNo) {
        return leftNo - rightNo;
    }

    const leftUnit = left.unitSortOrder ?? Number.MAX_SAFE_INTEGER;
    const rightUnit = right.unitSortOrder ?? Number.MAX_SAFE_INTEGER;

    if (leftUnit !== rightUnit) {
        return leftUnit - rightUnit;
    }

    const leftQuest = left.questNo ?? Number.MAX_SAFE_INTEGER;
    const rightQuest = right.questNo ?? Number.MAX_SAFE_INTEGER;

    if (leftQuest !== rightQuest) {
        return leftQuest - rightQuest;
    }

    return left.lineNumber - right.lineNumber;
};

/**
 * CSV テキストを QuestImportItem[] に変換する。
 * 各行は child_quest。Unit名 の組み合わせから personal_unit を自動生成する。
 */
export const parseQuestImportCsv = (
    text: string,
): { items: QuestImportItem[]; errors: string[] } => {
    const rows = parseCsvText(text);
    const errors: string[] = [];

    if (rows.length === 0) {
        return { items: [], errors: ['CSVにデータ行がありません。'] };
    }

    const [headerRow, ...dataRows] = rows;
    const headerIndex = buildCsvHeaderIndex(headerRow);

    const missingColumns = REQUIRED_COLUMNS.filter((column) => !headerIndex.has(column));
    if (missingColumns.length > 0) {
        const labels = missingColumns.map((column) =>
            column === 'unitTitle' ? 'Unit名' : 'Quest名',
        );
        errors.push(`必須列がありません: ${labels.join('、')}`);
        return { items: [], errors };
    }

    const parsedRows: ParsedCsvRow[] = [];
    const unitEntries = new Map<string, UnitEntry>();

    dataRows.forEach((cells, index) => {
        const lineNumber = index + 2;
        const unitTitle = getCsvCell(cells, headerIndex, 'unitTitle').trim();
        const title = getCsvCell(cells, headerIndex, 'title').trim();

        if (unitTitle === '' && title === '') {
            return;
        }

        if (unitTitle === '') {
            errors.push(`${lineNumber}行目: Unit名 が空です。`);
            return;
        }

        const contentRaw = getCsvCell(cells, headerIndex, 'description');
        const purposeRaw = getCsvCell(cells, headerIndex, 'purpose');
        const unitDescriptionRaw = getCsvCell(cells, headerIndex, 'unitDescription');
        const clearConditionRaw = getCsvCell(cells, headerIndex, 'clearCondition').trim();
        const { descriptionBody, extractedClearCondition } = splitContentBody(contentRaw);
        const description = buildDescription(descriptionBody, purposeRaw);
        const clearCondition = clearConditionRaw || extractedClearCondition;

        const csvNoRaw = getCsvCell(cells, headerIndex, 'csvNo').trim();
        const csvNoValue = parseIntegerColumn(csvNoRaw, lineNumber, 'No', errors);
        const unitSortOrder = parseIntegerColumn(
            getCsvCell(cells, headerIndex, 'unitSortOrder'),
            lineNumber,
            'Unit',
            errors,
        );
        const questNo = parseIntegerColumn(
            getCsvCell(cells, headerIndex, 'questNo'),
            lineNumber,
            'Quest',
            errors,
        );

        const unitMeta = {
            unitTitle,
            unitSortOrder,
            csvNoValue,
            lineNumber,
            unitDescription: unitDescriptionRaw,
            purpose: purposeRaw,
            descriptionBody,
        };

        if (title === '') {
            upsertUnitEntry(unitEntries, unitMeta);
            return;
        }

        parsedRows.push({
            lineNumber,
            csvNo: csvNoRaw,
            csvNoValue,
            unitSortOrder,
            questNo,
            todoNote: getCsvCell(cells, headerIndex, 'todoNote').trim(),
            unitTitle,
            title,
            description,
            descriptionBody,
            purpose: purposeRaw.trim(),
            unitDescription: unitDescriptionRaw.trim(),
            clearCondition,
            toolName: pickFirstToolName(getCsvCell(cells, headerIndex, 'toolName')),
            difficulty: getCsvCell(cells, headerIndex, 'difficulty').trim(),
            estimatedDuration: getCsvCell(cells, headerIndex, 'estimatedDuration').trim(),
        });

        upsertUnitEntry(unitEntries, unitMeta);
    });

    if (parsedRows.length === 0 && errors.length === 0) {
        return { items: [], errors: ['取り込めるデータ行がありません。'] };
    }

    const personalUnits: QuestImportItem[] = [...unitEntries.values()]
        .sort(sortUnitEntries)
        .map((unit) => ({
            clientId: createClientId(unit.lineNumber, '-unit'),
            kind: 'personal_unit' as const,
            title: unit.unitTitle,
            description: unit.description || undefined,
            sortOrder: unit.unitSortOrder ?? unit.minCsvNo,
            rewards: [],
            isPublished: false,
        }));

    const childQuests: QuestImportItem[] = parsedRows.sort(sortParsedRows).map((row) => ({
        clientId: createClientId(row.lineNumber),
        kind: 'child_quest' as const,
        csvNo: row.csvNo || undefined,
        unitSortOrder: row.unitSortOrder,
        unitTitle: row.unitTitle,
        title: row.title,
        description: row.description || undefined,
        clearCondition: row.clearCondition || undefined,
        toolCode: row.toolName || undefined,
        estimatedDuration: row.estimatedDuration || undefined,
        todoNote: row.todoNote || undefined,
        difficulty: row.difficulty || undefined,
        sortOrder: row.csvNoValue ?? row.questNo,
        rewards: [],
        isPublished: false,
    }));

    return { items: groupImportItemsByUnit([...personalUnits, ...childQuests]), errors };
};
