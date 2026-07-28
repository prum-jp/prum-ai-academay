import { reactive, ref } from 'vue';
import { createMentorQuest, createMentorQuestUnit } from '@/api/questAdmin';
import { mentorQuestAdminMessages } from '@/constants/questAdmin';
import type {
    CreateMentorQuestPayload,
    CreateMentorQuestUnitPayload,
    MentorQuestCreateType,
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

const createEmptyUnitForm = (): CreateMentorQuestUnitPayload => ({
    title: '',
    description: '',
    rewardText: '',
    rewards: [],
});

const createEmptyQuestForm = (
    type: Extract<MentorQuestCreateType, 'team' | 'special'>,
): CreateMentorQuestPayload => ({
    type,
    title: '',
    description: '',
    clearCondition: '',
    isRequired: true,
    unlockLevel: null,
    rewardText: '',
    badgeLabel: '',
    rewards: [],
});

export function useMentorQuestCreate() {
    const createType = ref<MentorQuestCreateType>('personal');
    const unitForm = reactive(createEmptyUnitForm());
    const questForm = reactive(createEmptyQuestForm('team'));
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

    const resetForms = (): void => {
        Object.assign(unitForm, createEmptyUnitForm());
        Object.assign(questForm, createEmptyQuestForm(
            createType.value === 'special' ? 'special' : 'team',
        ));
        clearErrors();
    };

    const setCreateType = (type: MentorQuestCreateType): void => {
        createType.value = type;
        clearErrors();

        if (type === 'team' || type === 'special') {
            Object.assign(questForm, createEmptyQuestForm(type));
        }
    };

    const submit = async (): Promise<boolean> => {
        if (isSubmitting.value) {
            return false;
        }

        isSubmitting.value = true;
        clearErrors();

        try {
            if (createType.value === 'personal') {
                await createMentorQuestUnit({
                    title: unitForm.title.trim(),
                    description: unitForm.description.trim(),
                    rewardText: unitForm.rewardText.trim(),
                    rewards: unitForm.rewards.map((reward) => ({
                        stat: reward.stat,
                        points: Number(reward.points),
                    })),
                });
            } else {
                await createMentorQuest({
                    type: questForm.type,
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
            }

            playSound('level-up');
            resetForms();
            return true;
        } catch (error: unknown) {
            playSound('down');
            const fields = createType.value === 'personal' ? UNIT_FIELDS : QUEST_FIELDS;
            Object.assign(fieldErrors, extractApiFieldErrors(error, fields));
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                createType.value === 'personal'
                    ? mentorQuestAdminMessages.createUnitFailed
                    : mentorQuestAdminMessages.createQuestFailed,
            );
            return false;
        } finally {
            isSubmitting.value = false;
        }
    };

    return {
        createType,
        unitForm,
        questForm,
        isSubmitting,
        errorMessage,
        fieldErrors,
        setCreateType,
        submit,
        resetForms,
    };
}
