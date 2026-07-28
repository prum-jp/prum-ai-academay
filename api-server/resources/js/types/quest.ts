export type QuestType = 'personal' | 'team' | 'special';

export interface QuestReward {
    stat: string;
    points: number;
}

export interface QuestTool {
    id: number;
    code: string;
    name: string;
    icon: string | null;
}

export interface QuestItem {
    id: number;
    title: string;
    description: string;
    type: QuestType;
    questUnitId?: number | null;
    tool: QuestTool | null;
    isRequired: boolean;
    unlockLevel: number | null;
    rewardText: string;
    rewards: QuestReward[];
    badgeLabel: string | null;
    brandLabel: string | null;
    clearCondition: string;
    sortOrder: number;
    startsAt: string | null;
    endsAt: string | null;
    isLocked: boolean;
    isCompleted: boolean;
    participantCount: number;
}

export interface QuestUnitItem {
    id: number;
    title: string;
    description: string;
    sortOrder: number;
    rewardText: string;
    rewards: QuestReward[];
    quests: QuestItem[];
    completedCount: number;
    totalCount: number;
    isCompleted: boolean;
}

export interface QuestListMeta {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
}

export interface QuestListResponse {
    data: QuestItem[];
    meta: QuestListMeta;
    studentLevel: number;
}

export interface QuestUnitListResponse {
    data: QuestUnitItem[];
    meta: QuestListMeta;
    studentLevel: number;
}
