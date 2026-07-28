export interface AdventurerStats {
    presentation: number;
    communication: number;
    problemFinding: number;
    aiAffinity: number;
    action: number;
    support: number;
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
    earnedBadgeCount: number;
    totalBadgeCount: number;
}

export type SoundType = 'click' | 'level-up' | 'down';
