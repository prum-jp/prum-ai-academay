import type { AdventurerStats } from '@/types/profile/adventurer';

export type SkillKey = keyof AdventurerStats;

export interface SkillDefinition {
    key: SkillKey;
    label: string;
    icon: string;
}

export const skillDefinitions: SkillDefinition[] = [
    { key: 'businessSkill', label: 'ビジネススキル', icon: 'fa-solid fa-briefcase' },
    { key: 'humanSkill', label: 'ヒューマンスキル', icon: 'fa-solid fa-people-group' },
    { key: 'conceptualSkill', label: 'コンセプチュアルスキル', icon: 'fa-solid fa-lightbulb' },
];

export const skillLabels: Record<SkillKey, string> = {
    businessSkill: 'ビジネススキル',
    humanSkill: 'ヒューマンスキル',
    conceptualSkill: 'コンセプチュアルスキル',
};

export const createEmptySkillGrants = (): SkillKey[] => [];

export const normalizeSkillGrants = (skills: readonly string[]): SkillKey[] => {
    const allowed = new Set<SkillKey>(skillDefinitions.map((item) => item.key));

    return [...new Set(skills.filter((skill): skill is SkillKey => allowed.has(skill as SkillKey)))];
};

export const skillGrantsFromLegacyRewards = (
    rewards: Array<{ skill?: string; stat?: string }>,
): SkillKey[] =>
    normalizeSkillGrants(
        rewards.map((reward) => reward.skill ?? reward.stat ?? '').filter(Boolean),
    );
