export interface AdventurerStats {
    businessSkill: number;
    humanSkill: number;
    conceptualSkill: number;
}

export interface AdventurerProfile {
    name: string;
    background: string;
    hobby: string;
    avatarUrl: string | null;
    weaponSkill: string;
    spellGoal: string;
    stats: AdventurerStats;
    level: number;
    levelTitle: string;
    total: number;
    progressPercent: number;
    xpCurrentLevelMin: number;
    xpNextLevelMin: number | null;
    earnedBadgeCount: number;
    totalBadgeCount: number;
}
