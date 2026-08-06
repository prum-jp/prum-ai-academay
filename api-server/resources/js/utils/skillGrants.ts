import type { SkillKey } from '@/constants/skills';
import { skillDefinitions, skillLabels } from '@/constants/skills';

export const formatSkillGrantLabel = (skill: SkillKey): string => skillLabels[skill];

export const formatSkillGrantReward = (skill: SkillKey): string => formatSkillGrantLabel(skill);

export const formatSkillGrants = (skills: SkillKey[]): string => {
    if (skills.length === 0) {
        return '—';
    }

    return skills.map((skill) => formatSkillGrantReward(skill)).join(' / ');
};

export const formatSkillGrantList = (skills: SkillKey[]): string[] =>
    skills.map((skill) => formatSkillGrantReward(skill));

export const findSkillDefinition = (skill: SkillKey) =>
    skillDefinitions.find((item) => item.key === skill);
