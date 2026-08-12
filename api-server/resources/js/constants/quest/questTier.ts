export type QuestTier = 'low' | 'medium' | 'high' | 'expert';

export const DEFAULT_QUEST_TIER: QuestTier = 'low';

export const QUEST_TIER_OPTIONS: ReadonlyArray<{
    value: QuestTier;
    label: string;
    requirement: string;
}> = [
    { value: 'low', label: '低クエスト', requirement: '全レベル' },
    { value: 'medium', label: '中クエスト', requirement: 'Lv.6以上' },
    { value: 'high', label: '高クエスト', requirement: 'Lv.9以上' },
    { value: 'expert', label: 'エキスパートクエスト', requirement: 'Lv.13以上' },
];

const TIER_ALIASES: Record<string, QuestTier> = {
    low: 'low',
    medium: 'medium',
    high: 'high',
    expert: 'expert',
    低: 'low',
    低クエスト: 'low',
    ロー: 'low',
    中: 'medium',
    中クエスト: 'medium',
    ミドル: 'medium',
    高: 'high',
    高クエスト: 'high',
    ハイ: 'high',
    エキスパート: 'expert',
    エキスパートクエスト: 'expert',
    上級: 'expert',
};

const UNLOCK_LEVEL_TO_TIER: Record<number, QuestTier> = {
    6: 'medium',
    9: 'high',
    13: 'expert',
};

export const parseQuestTier = (raw: string | null | undefined): QuestTier | undefined => {
    const trimmed = raw?.trim() ?? '';
    if (trimmed === '') {
        return undefined;
    }

    const normalized = trimmed.toLowerCase();
    return TIER_ALIASES[normalized] ?? TIER_ALIASES[trimmed];
};

export const resolveQuestTier = (
    tier: QuestTier | null | undefined,
    unlockLevel: number | null | undefined,
): QuestTier => {
    if (tier !== null && tier !== undefined) {
        return tier;
    }

    if (unlockLevel !== null && unlockLevel !== undefined) {
        return UNLOCK_LEVEL_TO_TIER[unlockLevel] ?? DEFAULT_QUEST_TIER;
    }

    return DEFAULT_QUEST_TIER;
};

export const formatQuestTierLabel = (tier: QuestTier): string =>
    QUEST_TIER_OPTIONS.find((option) => option.value === tier)?.label ?? '低クエスト';

export const formatQuestTierRequirement = (tier: QuestTier): string =>
    QUEST_TIER_OPTIONS.find((option) => option.value === tier)?.requirement ?? '全レベル';

export const formatQuestTierDisplay = (
    tier: QuestTier | null | undefined,
    unlockLevel: number | null | undefined = null,
): string => {
    const resolved = resolveQuestTier(tier, unlockLevel);
    const requirement = formatQuestTierRequirement(resolved);

    if (resolved === DEFAULT_QUEST_TIER) {
        return `${formatQuestTierLabel(resolved)}（${requirement}）`;
    }

    return `${formatQuestTierLabel(resolved)}（${requirement}）`;
};
