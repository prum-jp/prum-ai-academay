import type { AdventurerProfile, AdventurerStats } from '@/types/adventurer';

const toStars = (value: number): string => {
    return value > 0 ? '★'.repeat(value) : '☆';
};

export const formatAdventurerCard = (profile: AdventurerProfile): string => {
    const name = profile.name || '未設定の勇者';
    const background = profile.background || '未設定';
    const hobby = profile.hobby || '未設定';
    const weapon = profile.weaponSkill || 'これから入手！';
    const spell = profile.spellGoal || 'これから習得！';
    const stats: AdventurerStats = profile.stats;

    return `┌──────────────────────────┐
   ★ 冒険者カード ★
└──────────────────────────┘
【名前】 ${name}
【称号】 ${profile.levelTitle}

【前職】 ${background}
【趣味】 ${hobby}

【装備スキル】
⚔️ 武器(今できること):
${weapon}

🔮 呪文(学びたいAI魔法):
${spell}

【ビジネス戦闘力】
プレゼン：${toStars(stats.presentation)} (${stats.presentation})
コミュ力：${toStars(stats.communication)} (${stats.communication})
課題発見：${toStars(stats.problemFinding)} (${stats.problemFinding})
AI親和性：${toStars(stats.aiAffinity)} (${stats.aiAffinity})
行動力　：${toStars(stats.action)} (${stats.action})
お助け力：${toStars(stats.support)} (${stats.support})
────────────────────────────
PRUM AI Academy で「爆速」でレベルアップ中！`;
};
