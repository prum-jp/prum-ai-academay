import { reactive, ref } from 'vue';
import {
    deleteMentorQuest,
    deleteMentorQuestUnit,
    fetchMentorQuestUnitDetail,
    fetchMentorTools,
    updateMentorQuest,
    updateMentorQuestUnit,
} from '@/api/questAdmin';
import { mentorQuestAdminMessages } from '@/constants/questAdmin';
import type {
    MentorChildQuestInput,
    MentorQuestItem,
    MentorQuestRewardInput,
    MentorQuestUnitItem,
    MentorTool,
} from '@/types/questAdmin';
import { useGameAudio } from '@/composables/useGameAudio';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';
import { extractApiFieldErrors } from '@/utils/extractApiFieldErrors';

const UNIT_FIELDS = ['title', 'description', 'rewardText'] as const;
const QUEST_FIELDS = [
    'title',
    'description',
    'clearCondition',
    'rewardText',
    'badgeLabel',
    'unlockLevel',
] as const;

interface UnitEditForm {
    title: string;
    description: string;
    rewardText: string;
    rewards: MentorQuestRewardInput[];
    quests: MentorChildQuestInput[];
}

interface QuestEditForm {
    title: string;
    description: string;
    clearCondition: string;
    isRequired: boolean;
    unlockLevel: number | null;
    rewardText: string;
    badgeLabel: string;
    rewards: MentorQuestRewardInput[];
}

const createUnitForm = (): UnitEditForm => ({
    title: '',
    description: '',
    rewardText: '',
    rewards: [],
    quests: [],
});

const createQuestForm = (): QuestEditForm => ({
    title: '',
    description: '',
    clearCondition: '',
    isRequired: true,
    unlockLevel: null,
    rewardText: '',
    badgeLabel: '',
    rewards: [],
});

export function useMentorQuestEdit() {
    const tools = ref<MentorTool[]>([]);
    const unitForm = reactive(createUnitForm());
    const questForm = reactive(createQuestForm());
    const editUnitId = ref<number | null>(null);
    const editQuestId = ref<number | null>(null);
    const isLoading = ref(false);
    const isSubmitting = ref(false);
    const errorMessage = ref('');
    const fieldErrors = reactive<Record<string, string>>({});

    const { playSound } = useGameAudio();

    const clearErrors = (): void => {
        errorMessage.value = '';
        for (const key of Object.keys(fieldErrors)) {
            delete fieldErrors[key];
        }
    };

    const ensureTools = async (): Promise<void> => {
        if (tools.value.length > 0) {
            return;
        }

        try {
            tools.value = await fetchMentorTools();
        } catch {
            tools.value = [];
        }
    };

    const initUnit = async (unit: MentorQuestUnitItem): Promise<void> => {
        clearErrors();
        editUnitId.value = unit.id;
        Object.assign(unitForm, createUnitForm());
        isLoading.value = true;

        await ensureTools();

        try {
            const detail = await fetchMentorQuestUnitDetail(unit.id);
            unitForm.title = detail.title;
            unitForm.description = detail.description;
            unitForm.rewardText = detail.rewardText;
            unitForm.rewards = detail.rewards.map((reward) => ({ ...reward }));
            unitForm.quests = detail.quests.map((quest) => ({
                id: quest.id,
                title: quest.title,
                description: quest.description,
                clearCondition: quest.clearCondition,
                toolId: quest.toolId,
                sortOrder: quest.sortOrder,
                isPublished: quest.isPublished,
            }));
        } catch {
            errorMessage.value = mentorQuestAdminMessages.loadUnitDetailFailed;
        } finally {
            isLoading.value = false;
        }
    };

    const initQuest = (quest: MentorQuestItem): void => {
        clearErrors();
        editQuestId.value = quest.id;
        Object.assign(questForm, {
            title: quest.title,
            description: quest.description,
            clearCondition: quest.clearCondition,
            isRequired: quest.isRequired,
            unlockLevel: quest.unlockLevel,
            rewardText: quest.rewardText,
            badgeLabel: quest.badgeLabel ?? '',
            rewards: quest.rewards.map((reward) => ({ ...reward })),
        });
    };

    const addChildQuest = (): void => {
        unitForm.quests.push({
            id: null,
            title: '',
            description: '',
            clearCondition: '',
            toolId: null,
            sortOrder: unitForm.quests.length + 1,
            isPublished: true,
        });
    };

    const removeChildQuest = (index: number): void => {
        unitForm.quests.splice(index, 1);
    };

    const submitUnit = async (): Promise<boolean> => {
        if (isSubmitting.value || editUnitId.value === null) {
            return false;
        }

        isSubmitting.value = true;
        clearErrors();

        try {
            await updateMentorQuestUnit(editUnitId.value, {
                title: unitForm.title.trim(),
                description: unitForm.description.trim(),
                rewardText: unitForm.rewardText.trim(),
                rewards: unitForm.rewards.map((reward) => ({
                    stat: reward.stat,
                    points: Number(reward.points),
                })),
                quests: unitForm.quests.map((quest, index) => ({
                    id: quest.id,
                    title: quest.title.trim(),
                    description: quest.description.trim(),
                    clearCondition: quest.clearCondition.trim(),
                    toolId: quest.toolId,
                    sortOrder: index + 1,
                    isPublished: quest.isPublished,
                })),
            });
            playSound('level-up');
            return true;
        } catch (error: unknown) {
            playSound('down');
            Object.assign(fieldErrors, extractApiFieldErrors(error, UNIT_FIELDS));
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                mentorQuestAdminMessages.updateUnitFailed,
            );
            return false;
        } finally {
            isSubmitting.value = false;
        }
    };

    const submitQuest = async (): Promise<boolean> => {
        if (isSubmitting.value || editQuestId.value === null) {
            return false;
        }

        isSubmitting.value = true;
        clearErrors();

        try {
            await updateMentorQuest(editQuestId.value, {
                title: questForm.title.trim(),
                description: questForm.description.trim(),
                clearCondition: questForm.clearCondition.trim(),
                isRequired: questForm.isRequired,
                unlockLevel: questForm.unlockLevel ? Number(questForm.unlockLevel) : null,
                rewardText: questForm.rewardText.trim(),
                badgeLabel: questForm.badgeLabel.trim(),
                rewards: questForm.rewards.map((reward) => ({
                    stat: reward.stat,
                    points: Number(reward.points),
                })),
            });
            playSound('level-up');
            return true;
        } catch (error: unknown) {
            playSound('down');
            Object.assign(fieldErrors, extractApiFieldErrors(error, QUEST_FIELDS));
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                mentorQuestAdminMessages.updateQuestFailed,
            );
            return false;
        } finally {
            isSubmitting.value = false;
        }
    };

    return {
        tools,
        unitForm,
        questForm,
        isLoading,
        isSubmitting,
        errorMessage,
        fieldErrors,
        initUnit,
        initQuest,
        addChildQuest,
        removeChildQuest,
        submitUnit,
        submitQuest,
    };
}

export function useMentorQuestDelete() {
    const isDeleting = ref(false);
    const { playSound } = useGameAudio();

    const removeUnit = async (id: number): Promise<boolean> => {
        if (isDeleting.value) {
            return false;
        }

        isDeleting.value = true;
        try {
            await deleteMentorQuestUnit(id);
            playSound('down');
            return true;
        } catch {
            playSound('down');
            return false;
        } finally {
            isDeleting.value = false;
        }
    };

    const removeQuest = async (id: number): Promise<boolean> => {
        if (isDeleting.value) {
            return false;
        }

        isDeleting.value = true;
        try {
            await deleteMentorQuest(id);
            playSound('down');
            return true;
        } catch {
            playSound('down');
            return false;
        } finally {
            isDeleting.value = false;
        }
    };

    return {
        isDeleting,
        removeUnit,
        removeQuest,
    };
}
