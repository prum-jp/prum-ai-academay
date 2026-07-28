import type { QuestListMeta, QuestReward, QuestType } from '@/types/quest';

export type MentorQuestCreateType = 'personal' | 'team' | 'special';

export interface MentorQuestUnitItem {
    id: number;
    title: string;
    description: string;
    sortOrder: number;
    rewardText: string;
    rewards: QuestReward[];
    questCount: number;
    isPublished: boolean;
}

export interface MentorQuestItem {
    id: number;
    title: string;
    description: string;
    type: Extract<QuestType, 'team' | 'special'>;
    isRequired: boolean;
    unlockLevel: number | null;
    rewardText: string;
    rewards: QuestReward[];
    badgeLabel: string | null;
    clearCondition: string;
    sortOrder: number;
    startsAt: string | null;
    endsAt: string | null;
    participantCount: number;
    isPublished: boolean;
}

export interface MentorQuestUnitListResponse {
    data: MentorQuestUnitItem[];
    meta: QuestListMeta;
}

export interface MentorQuestListResponse {
    data: MentorQuestItem[];
    meta: QuestListMeta;
}

export interface MentorQuestRewardInput {
    stat: string;
    points: number;
}

export interface CreateMentorQuestUnitPayload {
    title: string;
    description: string;
    rewardText: string;
    rewards: MentorQuestRewardInput[];
}

export interface CreateMentorQuestPayload {
    type: Extract<QuestType, 'team' | 'special'>;
    title: string;
    description: string;
    clearCondition: string;
    isRequired: boolean;
    unlockLevel: number | null;
    rewardText: string;
    badgeLabel: string;
    rewards: MentorQuestRewardInput[];
}

export interface MentorTool {
    id: number;
    code: string;
    name: string;
    icon: string | null;
}

export interface MentorChildQuestInput {
    id: number | null;
    title: string;
    description: string;
    clearCondition: string;
    toolId: number | null;
    sortOrder: number;
    isPublished: boolean;
}

export interface MentorQuestUnitDetail {
    id: number;
    title: string;
    description: string;
    sortOrder: number;
    rewardText: string;
    rewards: QuestReward[];
    quests: Array<{
        id: number;
        title: string;
        description: string;
        clearCondition: string;
        toolId: number | null;
        sortOrder: number;
        isPublished: boolean;
    }>;
}

export interface UpdateMentorQuestUnitPayload {
    title: string;
    description: string;
    rewardText: string;
    rewards: MentorQuestRewardInput[];
    quests: Array<{
        id: number | null;
        title: string;
        description: string;
        clearCondition: string;
        toolId: number | null;
        sortOrder: number;
        isPublished: boolean;
    }>;
}

export interface UpdateMentorQuestPayload {
    title: string;
    description: string;
    clearCondition: string;
    isRequired: boolean;
    unlockLevel: number | null;
    rewardText: string;
    badgeLabel: string;
    rewards: MentorQuestRewardInput[];
}
