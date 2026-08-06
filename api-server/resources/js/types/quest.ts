import type { QuestProgressStatus } from '@/constants/questProgress';
import type { UnitProgressStatus } from '@/constants/unitProgress';
import type { PaginationMeta } from '@/types/pagination';
import type { SkillKey } from '@/constants/skills';
import type { QuestTier } from '@/constants/questTier';
import type { QuestSubmission } from '@/types/questSubmission';

export type QuestType = 'personal' | 'team' | 'special';

export type { QuestProgressStatus, UnitProgressStatus };

export interface QuestReward {
    skill: SkillKey;
    points: 1;
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
    questTier?: QuestTier;
    rewardText: string;
    rewards: QuestReward[];
    badgeLabel: string | null;
    brandLabel: string | null;
    clearCondition: string;
    estimatedDuration: string | null;
    difficulty: number | null;
    experiencePoints: number;
    skillGrants: SkillKey[];
    sortOrder: number;
    unitTitle?: string | null;
    unitSortOrder?: number | null;
    startsAt: string | null;
    endsAt: string | null;
    isLocked: boolean;
    isCompleted: boolean;
    progressStatus: QuestProgressStatus;
    submission: QuestSubmission | null;
    submissionUrl: string | null;
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
    progressStatus: UnitProgressStatus;
}

export interface QuestListMeta extends PaginationMeta {}

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
