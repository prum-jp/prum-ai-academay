import type { SkillKey } from '@/constants/skills';

/** CSV ヘッダー別名 → 論理列キー */
export const QUEST_IMPORT_COLUMN_ALIASES: Record<string, string[]> = {
    csvNo: ['no'],
    unitSortOrder: ['unit'],
    questNo: ['quest'],
    mustFlag: ['must'],
    todoNote: ['to do', 'todo'],
    unitTitle: ['unit名', 'unit_name'],
    title: ['quest名', 'quest_name'],
    overview: ['概要', '内容', 'description'],
    purpose: ['目的', 'purpose'],
    deliverable: ['提出物', 'deliverable'],
    completionCondition: ['完了条件', 'クリア条件', 'clear_condition'],
    toolName: ['ツール', 'tool'],
    difficulty: ['難度', 'difficulty', 'レベル', 'level'],
    experiencePoints: ['xp', '経験値', 'experience_points', 'experiencepoints'],
    questTier: ['クエスト段階', 'ティア', 'tier', 'quest_tier'],
};

/** スキル列ヘッダー → skill key（値が入っていれば付与） */
export const QUEST_IMPORT_SKILL_COLUMN_ALIASES: Record<SkillKey, string[]> = {
    businessSkill: ['ビジネス戦闘力', 'ビジネススキル', 'business_skill'],
    humanSkill: ['ヒューマン戦闘力', 'ヒューマンスキル', 'human_skill'],
    conceptualSkill: ['コンセプチュアル戦闘力', 'コンセプチュアルスキル', 'conceptual_skill'],
};

/** 取り込み対象外のヘッダー（数値のみなど） */
export const QUEST_IMPORT_IGNORED_HEADERS = new Set(['64']);

export const normalizeCsvHeader = (header: string): string => header.trim().toLowerCase();

export const buildCsvHeaderIndex = (headerRow: string[]): Map<string, number> => {
    const normalizedToIndex = new Map<string, number>();

    headerRow.forEach((header, index) => {
        const normalized = normalizeCsvHeader(header);
        if (QUEST_IMPORT_IGNORED_HEADERS.has(normalized)) {
            return;
        }

        normalizedToIndex.set(normalized, index);
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

export const buildSkillColumnIndex = (headerRow: string[]): Map<SkillKey, number> => {
    const normalizedToIndex = new Map<string, number>();

    headerRow.forEach((header, index) => {
        const normalized = normalizeCsvHeader(header);
        if (QUEST_IMPORT_IGNORED_HEADERS.has(normalized)) {
            return;
        }

        normalizedToIndex.set(normalized, index);
    });

    const skillIndex = new Map<SkillKey, number>();

    for (const [skillKey, aliases] of Object.entries(QUEST_IMPORT_SKILL_COLUMN_ALIASES)) {
        for (const alias of aliases) {
            const index = normalizedToIndex.get(alias);
            if (index !== undefined) {
                skillIndex.set(skillKey as SkillKey, index);
                break;
            }
        }
    }

    headerRow.forEach((header, index) => {
        const normalized = normalizeCsvHeader(header);

        if (normalized.includes('ビジネス') && !skillIndex.has('businessSkill')) {
            skillIndex.set('businessSkill', index);
        }

        if (normalized.includes('ヒューマン') && !skillIndex.has('humanSkill')) {
            skillIndex.set('humanSkill', index);
        }

        if (normalized.includes('コンセプチュアル') && !skillIndex.has('conceptualSkill')) {
            skillIndex.set('conceptualSkill', index);
        }
    });

    return skillIndex;
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

export const getCsvCellByIndex = (cells: string[], columnIndex: number | undefined): string => {
    if (columnIndex === undefined) {
        return '';
    }

    return cells[columnIndex] ?? '';
};
