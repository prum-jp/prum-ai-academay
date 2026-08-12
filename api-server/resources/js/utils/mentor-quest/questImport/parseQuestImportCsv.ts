import type { QuestImportItem } from '@/types/mentor-quest/questImport';
import { parseQuestDifficulty } from '@/utils/quest/questDifficulty';
import { DEFAULT_QUEST_TIER, parseQuestTier, type QuestTier } from '@/constants/quest/questTier';
import {
    buildCsvHeaderIndex,
    buildSkillColumnIndex,
    getCsvCell,
    QUEST_IMPORT_COLUMN_ALIASES,
} from '@/utils/mentor-quest/questImport/columns';
import { parseCsvText } from '@/utils/mentor-quest/questImport/csvParser';
import { groupImportItemsByUnit } from '@/utils/mentor-quest/questImport/groupItems';
import { parseMustFlag } from '@/utils/mentor-quest/questImport/parseMustFlag';
import { parseSkillGrantsFromCsvRow } from '@/utils/mentor-quest/questImport/parseSkillGrantsFromCsvRow';
import { pickFirstToolName } from '@/utils/mentor-quest/questImport/splitToolNames';
import { normalizeQuestSheetBodyText } from '@/utils/quest-sheet/questSheetBody';

/**
 * CSV列 → QuestImportItem（概要/目的/提出物/完了条件は列ごとに保持）
 * API 反映時に description / clear_condition へ変換する。
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

interface ParsedCsvRow {
    lineNumber: number;
    csvNo: string;
    csvNoValue?: number;
    unitSortOrder?: number;
    questNo?: number;
    todoNote: string;
    unitTitle: string;
    title: string;
    overview: string;
    purpose: string;
    deliverable: string;
    completionCondition: string;
    isRequired: boolean;
    toolName: string;
    difficulty: string;
    questTier: string;
    skillGrants: QuestImportItem['skillGrants'];
}

interface UnitEntry {
    unitTitle: string;
    unitSortOrder?: number;
    minCsvNo?: number;
    lineNumber: number;
}

const upsertUnitEntry = (
    unitEntries: Map<string, UnitEntry>,
    row: {
        unitTitle: string;
        unitSortOrder?: number;
        csvNoValue?: number;
        lineNumber: number;
    },
): void => {
    const key = row.unitTitle;
    const existing = unitEntries.get(key);

    if (!existing) {
        unitEntries.set(key, {
            unitTitle: row.unitTitle,
            unitSortOrder: row.unitSortOrder,
            minCsvNo: row.csvNoValue,
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
 * ユニット行（Quest名が空）は Unit名 と Unit（並び順）のみ登録する。
 */
export const parseQuestImportCsv = (
    text: string,
    options?: {
        defaultQuestTier?: QuestTier;
    },
): { items: QuestImportItem[]; errors: string[] } => {
    const defaultQuestTier = options?.defaultQuestTier ?? DEFAULT_QUEST_TIER;
    const rows = parseCsvText(text);
    const errors: string[] = [];

    if (rows.length === 0) {
        return { items: [], errors: ['CSVにデータ行がありません。'] };
    }

    const [headerRow, ...dataRows] = rows;
    const headerIndex = buildCsvHeaderIndex(headerRow);
    const skillColumnIndex = buildSkillColumnIndex(headerRow);
    const mustColumnPresent = headerIndex.has('mustFlag');

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

        const overview = normalizeQuestSheetBodyText(
            getCsvCell(cells, headerIndex, 'overview').trim(),
        );
        const purpose = normalizeQuestSheetBodyText(
            getCsvCell(cells, headerIndex, 'purpose').trim(),
        );
        const deliverable = normalizeQuestSheetBodyText(
            getCsvCell(cells, headerIndex, 'deliverable').trim(),
        );
        const completionCondition = normalizeQuestSheetBodyText(
            getCsvCell(cells, headerIndex, 'completionCondition').trim(),
        );
        const isRequired = parseMustFlag(getCsvCell(cells, headerIndex, 'mustFlag'), {
            columnPresent: mustColumnPresent,
        });

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
            overview,
            purpose,
            deliverable,
            completionCondition,
            isRequired,
            toolName: pickFirstToolName(getCsvCell(cells, headerIndex, 'toolName')),
            difficulty: getCsvCell(cells, headerIndex, 'difficulty').trim(),
            questTier: getCsvCell(cells, headerIndex, 'questTier').trim(),
            skillGrants: parseSkillGrantsFromCsvRow(cells, skillColumnIndex),
        });

        upsertUnitEntry(unitEntries, unitMeta);
    });

    if (parsedRows.length === 0 && unitEntries.size === 0 && errors.length === 0) {
        return { items: [], errors: ['取り込めるデータ行がありません。'] };
    }

    const personalUnits: QuestImportItem[] = [...unitEntries.values()]
        .sort(sortUnitEntries)
        .map((unit) => ({
            clientId: createClientId(unit.lineNumber, '-unit'),
            kind: 'personal_unit' as const,
            title: unit.unitTitle,
            sortOrder: unit.unitSortOrder ?? unit.minCsvNo,
            skillGrants: [],
        }));

    const childQuests: QuestImportItem[] = parsedRows.sort(sortParsedRows).map((row) => ({
        clientId: createClientId(row.lineNumber),
        kind: 'child_quest' as const,
        csvNo: row.csvNo || undefined,
        unitSortOrder: row.unitSortOrder,
        unitTitle: row.unitTitle,
        title: row.title,
        overview: row.overview || undefined,
        purpose: row.purpose || undefined,
        deliverable: row.deliverable || undefined,
        completionCondition: row.completionCondition || undefined,
        isRequired: row.isRequired,
        toolCode: row.toolName || undefined,
        todoNote: row.todoNote || undefined,
        difficulty: parseQuestDifficulty(row.difficulty),
        questTier: parseQuestTier(row.questTier) ?? defaultQuestTier,
        sortOrder: row.csvNoValue ?? row.questNo,
        skillGrants: row.skillGrants,
    }));

    return { items: groupImportItemsByUnit([...personalUnits, ...childQuests]), errors };
};
