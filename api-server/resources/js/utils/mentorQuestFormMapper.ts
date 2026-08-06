import type { MentorQuestDetail } from '@/types/questAdmin';
import { createEmptySkillGrants } from '@/constants/skills';
import { DEFAULT_QUEST_TIER, type QuestTier } from '@/constants/questTier';
import {
    createEmptyQuestDescriptionSections,
    parseQuestDescriptionSections,
    type QuestDescriptionSections,
} from '@/utils/questDescriptionSections';
import type { SkillKey } from '@/constants/skills';

export interface MentorQuestDisplayForm {
    title: string;
    toolId: number | null;
    difficulty: number | null;
    questTier: QuestTier;
    skillGrants: SkillKey[];
}

export interface MentorQuestBoardMetaForm {
    unlockLevel: number | null;
    badgeLabel: string;
    rewardText: string;
    difficulty: number | null;
    isRequired: boolean;
    skillGrants: SkillKey[];
}

export function createEmptyMentorQuestDisplayForm(): MentorQuestDisplayForm {
    return {
        title: '',
        toolId: null,
        difficulty: null,
        questTier: DEFAULT_QUEST_TIER,
        skillGrants: createEmptySkillGrants(),
    };
}

export function createEmptyMentorQuestBoardMetaForm(): MentorQuestBoardMetaForm {
    return {
        unlockLevel: null,
        badgeLabel: '',
        rewardText: '',
        difficulty: null,
        isRequired: true,
        skillGrants: createEmptySkillGrants(),
    };
}

export function mapQuestDetailToDisplayForms(detail: MentorQuestDetail): {
    displayForm: MentorQuestDisplayForm;
    boardMetaForm: MentorQuestBoardMetaForm;
    sectionForm: QuestDescriptionSections;
} {
    return {
        displayForm: {
            title: detail.title,
            toolId: detail.toolId,
            difficulty: detail.difficulty,
            questTier: detail.questTier ?? DEFAULT_QUEST_TIER,
            skillGrants: detail.skillGrants ?? createEmptySkillGrants(),
        },
        boardMetaForm: {
            unlockLevel: detail.unlockLevel,
            badgeLabel: detail.badgeLabel ?? '',
            rewardText: detail.rewardText,
            difficulty: detail.difficulty,
            isRequired: detail.isRequired,
            skillGrants: detail.skillGrants ?? createEmptySkillGrants(),
        },
        sectionForm: parseQuestDescriptionSections(detail.description, detail.clearCondition),
    };
}

export function createEmptyQuestDetailForms(): {
    displayForm: MentorQuestDisplayForm;
    boardMetaForm: MentorQuestBoardMetaForm;
    sectionForm: QuestDescriptionSections;
} {
    return {
        displayForm: createEmptyMentorQuestDisplayForm(),
        boardMetaForm: createEmptyMentorQuestBoardMetaForm(),
        sectionForm: createEmptyQuestDescriptionSections(),
    };
}
