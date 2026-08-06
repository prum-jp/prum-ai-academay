import type { QuestListMeta, QuestType } from '@/types/quest';
import type { SkillKey } from '@/constants/skills';
import type { QuestTier } from '@/constants/questTier';

export type MentorQuestCreateType = 'personal' | 'team' | 'special';

export interface MentorQuestUnitItem {
    id: number;
    title: string;
    description: string;
    sortOrder: number;
    rewardText: string;
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
    badgeLabel: string | null;
    difficulty: number | null;
    experiencePoints: number;
    skillGrants: SkillKey[];
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

export interface CreateMentorQuestUnitChildQuestPayload {
    title: string;
    description: string;
    clearCondition: string;
    toolId: number | null;
    sortOrder: number;
    difficulty?: number | null;
    estimatedDuration?: string;
    skillGrants?: SkillKey[];
    questTier?: QuestTier;
}

export interface CreateMentorQuestUnitPayload {
    title: string;
    quests?: CreateMentorQuestUnitChildQuestPayload[];
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
    difficulty: number | null;
    skillGrants: SkillKey[];
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
    difficulty?: number | null;
    estimatedDuration?: string;
    skillGrants: SkillKey[];
    questTier: QuestTier;
}

export interface MentorQuestUnitDetail {
    id: number;
    title: string;
    description: string;
    sortOrder: number;
    rewardText: string;
    quests: Array<{
        id: number;
        title: string;
        description: string;
        clearCondition: string;
        toolId: number | null;
        sortOrder: number;
        isPublished: boolean;
        difficulty: number | null;
        estimatedDuration: string | null;
        experiencePoints: number;
        skillGrants: SkillKey[];
        questTier: QuestTier;
    }>;
}

export interface UpdateMentorQuestUnitPayload {
    title: string;
    quests: Array<{
        id: number | null;
        title: string;
        description: string;
        clearCondition: string;
        toolId: number | null;
        sortOrder: number;
        difficulty?: number | null;
        estimatedDuration?: string;
        skillGrants?: SkillKey[];
        questTier?: QuestTier;
    }>;
}

export interface MentorQuestDetail {
    id: number;
    title: string;
    description: string;
    clearCondition: string;
    type: QuestType;
    sortOrder: number;
    toolId: number | null;
    tool: { id: number; code: string; name: string } | null;
    estimatedDuration: string | null;
    difficulty: number | null;
    experiencePoints: number;
    skillGrants: SkillKey[];
    unitId: number | null;
    unitTitle?: string | null;
    isRequired: boolean;
    unlockLevel: number | null;
    questTier?: QuestTier;
    rewardText: string;
    badgeLabel: string | null;
    isPublished: boolean;
}

export interface UpdateMentorPersonalQuestPayload {
    title: string;
    description: string;
    clearCondition: string;
    toolId: number | null;
    estimatedDuration?: string;
    difficulty: number | null;
    skillGrants: SkillKey[];
    questTier?: QuestTier;
}

export interface UpdateMentorQuestPayload {
    title: string;
    description: string;
    clearCondition: string;
    isRequired: boolean;
    unlockLevel: number | null;
    rewardText: string;
    badgeLabel: string;
    difficulty: number | null;
    skillGrants: SkillKey[];
}
