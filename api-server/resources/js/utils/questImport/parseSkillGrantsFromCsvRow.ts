import { skillDefinitions, type SkillKey } from '@/constants/skills';
import { getCsvCellByIndex } from '@/utils/questImport/columns';

const INACTIVE_SKILL_VALUES = new Set(['', '-', '—', '×', 'x', 'X', 'なし', '無', '無し']);

const ACTIVE_MARKERS = new Set(['◯', '○', '〇', '●', '1', 'o', 'O', 'yes', 'y', 'true']);

const splitSkillTokens = (raw: string): string[] =>
    raw
        .split(/[,、/／|]/)
        .map((token) => token.trim())
        .filter(Boolean);

/** セル値から付与スキルを判定（列のスキル + ラベル名の列挙に対応） */
const parseSkillsFromCell = (raw: string, columnSkill: SkillKey): SkillKey[] => {
    const trimmed = raw.trim();
    if (trimmed === '' || INACTIVE_SKILL_VALUES.has(trimmed)) {
        return [];
    }

    const grants = new Set<SkillKey>();
    const columnDefinition = skillDefinitions.find((skill) => skill.key === columnSkill);
    const tokens = splitSkillTokens(trimmed);

    for (const token of tokens.length > 0 ? tokens : [trimmed]) {
        if (ACTIVE_MARKERS.has(token) || ACTIVE_MARKERS.has(token.toLowerCase())) {
            grants.add(columnSkill);
            continue;
        }

        let matched = false;

        for (const skill of skillDefinitions) {
            if (token === skill.label || token.includes(skill.label)) {
                grants.add(skill.key);
                matched = true;
            }
        }

        if (!matched && columnDefinition && (token === columnDefinition.label || token.includes(columnDefinition.label))) {
            grants.add(columnSkill);
        }
    }

    if (grants.size === 0 && trimmed !== '') {
        grants.add(columnSkill);
    }

    return [...grants];
};

export const parseSkillGrantsFromCsvRow = (
    cells: string[],
    skillColumnIndex: Map<SkillKey, number>,
): SkillKey[] => {
    const grants = new Set<SkillKey>();

    for (const skill of skillDefinitions) {
        const columnIndex = skillColumnIndex.get(skill.key);
        if (columnIndex === undefined) {
            continue;
        }

        for (const grant of parseSkillsFromCell(getCsvCellByIndex(cells, columnIndex), skill.key)) {
            grants.add(grant);
        }
    }

    return [...grants];
};
