import type { AdventurerProfile, AdventurerStats } from '@/types/adventurer';

const toStars = (value: number): string => {
    return value > 0 ? '★'.repeat(Math.min(value, 10)) : '☆';
};

export const formatAdventurerCard = (profile: AdventurerProfile): string => {
    const name = profile.name || '未設定';
    const background = profile.background || '未設定';
    const hobby = profile.hobby || '未設定';
    const weapon = profile.weaponSkill || '未設定';
    const spell = profile.spellGoal || '未設定';
    const stats: AdventurerStats = profile.stats;

    return `┌──────────────────────────┐
   ★ 受講者カード ★
└──────────────────────────┘
【名前】 ${name}
【称号】 ${profile.levelTitle}

【前職】 ${background}
【趣味】 ${hobby}

今できること / 得意な業務:
${weapon}

習得したいAIスキル:
${spell}

【スキル】
ビジネス：${toStars(stats.businessSkill)} (${stats.businessSkill})
ヒューマン：${toStars(stats.humanSkill)} (${stats.humanSkill})
コンセプチュアル：${toStars(stats.conceptualSkill)} (${stats.conceptualSkill})
────────────────────────────
PRUM AI Academy 受講中`;
};
