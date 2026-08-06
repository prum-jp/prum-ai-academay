import type { QuestItem, QuestType } from '@/types/quest';
import { questProgressStatusLabels } from '@/constants/questProgress';
import { questSheetConfig } from '@/constants/questSheet';
import {
    formatQuestDifficulty,
    formatQuestSkillLabel,
    resolveQuestExperiencePoints,
} from '@/utils/questSheetMeta';
import { formatExperiencePoints } from '@/utils/questDifficulty';
import { formatSkillGrantReward } from '@/utils/skillGrants';

export type QuestBadgeVariant = 'is-welcome' | 'is-lock' | 'is-default';
export type QuestStatusClass = 'is-locked' | 'is-completed' | 'is-open';

export interface QuestDetailFact {
    label: string;
    value: string;
}

export const getQuestBadgeVariant = (quest: QuestItem): QuestBadgeVariant => {
    if (quest.type === 'special') {
        return 'is-welcome';
    }
    if (quest.isLocked || quest.unlockLevel !== null) {
        return 'is-lock';
    }
    return 'is-default';
};

export const showParticipantCount = (quest: QuestItem): boolean => {
    return quest.type !== 'personal';
};

export const getQuestTypeLabel = (type: QuestType): string => {
    switch (type) {
        case 'personal':
            return '個人クエスト';
        case 'team':
            return 'チームクエスト';
        case 'special':
            return '特別クエスト';
        default:
            return 'クエスト';
    }
};

export const getQuestStatusLabel = (quest: QuestItem): string => {
    if (quest.isLocked) {
        return '未解放';
    }

    return questProgressStatusLabels[quest.progressStatus];
};

export const getQuestStatusClass = (quest: QuestItem): QuestStatusClass => {
    if (quest.isLocked) {
        return 'is-locked';
    }
    if (quest.progressStatus === 'completed') {
        return 'is-completed';
    }
    return 'is-open';
};

export const formatQuestReward = (reward: { skill: string }): string =>
    formatSkillGrantReward(reward.skill as import('@/constants/skills').SkillKey);

export const formatQuestDate = (value: string | null): string | null => {
    if (!value) {
        return null;
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return new Intl.DateTimeFormat('ja-JP', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};

export const formatQuestPeriod = (quest: QuestItem): string => {
    const startsAt = formatQuestDate(quest.startsAt);
    const endsAt = formatQuestDate(quest.endsAt);

    if (startsAt && endsAt) {
        return `${startsAt} 〜 ${endsAt}`;
    }
    if (startsAt) {
        return `${startsAt} 〜`;
    }
    if (endsAt) {
        return `〜 ${endsAt}`;
    }
    return '期間制限なし';
};

export const formatUnlockLevel = (unlockLevel: number | null): string => {
    return unlockLevel !== null ? `Lv.${unlockLevel}以上` : '制限なし';
};

export const formatQuestLockLabel = (quest: QuestItem): string => {
    if (!quest.isLocked) {
        return '';
    }

    if (quest.unlockLevel !== null) {
        return `${formatUnlockLevel(quest.unlockLevel)}で解放`;
    }

    return '未解放';
};

export const formatRequiredLabel = (isRequired: boolean): string => {
    return isRequired ? '必須クエスト' : '任意';
};

export const getQuestDetailFacts = (quest: QuestItem): QuestDetailFact[] => {
    const { metaLabels } = questSheetConfig;
    const facts: QuestDetailFact[] = [
        { label: '種別', value: getQuestTypeLabel(quest.type) },
        { label: metaLabels.difficulty, value: formatQuestDifficulty(quest) },
        {
            label: metaLabels.experiencePoints,
            value: formatExperiencePoints(resolveQuestExperiencePoints(quest)),
        },
        { label: metaLabels.acquiredSkill, value: formatQuestSkillLabel(quest) },
        { label: '必須', value: formatRequiredLabel(quest.isRequired) },
        { label: '解放レベル', value: formatUnlockLevel(quest.unlockLevel) },
        { label: '期間', value: formatQuestPeriod(quest) },
    ];

    if (showParticipantCount(quest)) {
        facts.push({
            label: '参加人数',
            value: `${quest.participantCount}人`,
        });
    }

    return facts;
};
