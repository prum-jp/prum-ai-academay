import type { SkillKey } from '@/constants/shared/skills';
import { formatSkillGrantLabel } from '@/utils/quest/skillGrants';

const formatUnitCode = (sortOrder: number): string =>
    `UNIT ${String(sortOrder).padStart(2, '0')}`;

export const formatUnitDisplayTitle = (sortOrder: number, title: string): string =>
    `${formatUnitCode(sortOrder)}：${title}`;

export const collectUnitSkillGrants = (
    quests: Array<{ skillGrants?: SkillKey[] }>,
): SkillKey[] => {
    const skills = new Set<SkillKey>();

    for (const quest of quests) {
        for (const skill of quest.skillGrants ?? []) {
            skills.add(skill);
        }
    }

    return [...skills];
};

export const formatUnitSkillGrantsText = (
    quests: Array<{ skillGrants?: SkillKey[] }>,
): string => {
    const skills = collectUnitSkillGrants(quests);
    if (skills.length === 0) {
        return '';
    }

    const labels = skills.map((skill) => `【${formatSkillGrantLabel(skill)}】`);

    return `スキル：${labels.join(' ')}`;
};

/** @deprecated use formatUnitSkillGrantsText */
export const formatGrowthStatusText = formatUnitSkillGrantsText;

/** @deprecated use formatSkillGrantLabel from skillGrants */
export const formatGrowthStatLabel = (stat: string): string => formatSkillGrantLabel(stat as SkillKey);
