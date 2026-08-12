import { computed, reactive, ref } from 'vue';
import { createMentorQuest, createMentorQuestUnit } from '@/api/mentor-quest/questAdmin';
import { mentorQuestAdminMessages } from '@/constants/mentor-quest/questAdmin';
import type {
    CreateMentorQuestPayload,
    CreateMentorQuestUnitPayload,
    CreateMentorQuestUnitChildQuestPayload,
    MentorQuestCreateType,
    MentorQuestUnitItem,
} from '@/types/mentor-quest/questAdmin';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';
import { extractApiFieldErrors } from '@/utils/shared/extractApiFieldErrors';
import { createEmptySkillGrants, type SkillKey } from '@/constants/shared/skills';
import { DEFAULT_QUEST_TIER, type QuestTier } from '@/constants/quest/questTier';
import {
    createEmptyQuestDescriptionSections,
    serializeQuestDescriptionSections,
    type QuestDescriptionSections,
} from '@/utils/quest-sheet/questDescriptionSections';

const UNIT_FIELDS = ['title'] as const;
const QUEST_FIELDS = [
    'title',
    'description',
    'clearCondition',
    'rewardText',
    'badgeLabel',
    'unlockLevel',
    'difficulty',
] as const;
const CHILD_QUEST_FIELDS = ['title', 'description', 'clearCondition'] as const;

export interface ChildQuestCreateDraft {
    title: string;
    toolIds: number[];
    difficulty: number | null;
    questTier: QuestTier;
    skillGrants: SkillKey[];
    sections: QuestDescriptionSections;
    sortOrder: number;
}

const createEmptyUnitForm = (): CreateMentorQuestUnitPayload => ({
    title: '',
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
    difficulty: null,
    skillGrants: createEmptySkillGrants(),
    toolIds: [],
});

const createEmptyChildQuestForm = () => ({
    title: '',
    toolIds: [] as number[],
    difficulty: null as number | null,
    questTier: DEFAULT_QUEST_TIER as QuestTier,
    skillGrants: createEmptySkillGrants(),
});

const copySections = (sections: QuestDescriptionSections): QuestDescriptionSections => ({
    overview: sections.overview,
    purpose: sections.purpose,
    deliverable: sections.deliverable,
    completionCondition: sections.completionCondition,
});

const buildChildQuestPayload = (
    draft: ChildQuestCreateDraft,
): CreateMentorQuestUnitChildQuestPayload => {
    const serialized = serializeQuestDescriptionSections(draft.sections);

    return {
        title: draft.title.trim(),
        description: serialized.description,
        clearCondition: serialized.clearCondition,
        toolIds: draft.toolIds,
        sortOrder: draft.sortOrder,
        difficulty: draft.difficulty,
        questTier: draft.questTier,
        skillGrants: draft.skillGrants,
    };
};

const renumberChildQuests = (quests: ChildQuestCreateDraft[]): ChildQuestCreateDraft[] =>
    quests.map((quest, index) => ({
        ...quest,
        sortOrder: index + 1,
    }));

export function useMentorQuestCreate(initialType: MentorQuestCreateType = 'team') {
    const createType = ref<MentorQuestCreateType>(initialType);
    const unitForm = reactive(createEmptyUnitForm());
    const questForm = reactive(createEmptyQuestForm(
        initialType === 'special' ? 'special' : 'team',
    ));
    const sectionForm = reactive(createEmptyQuestDescriptionSections());
    const childQuestForm = reactive(createEmptyChildQuestForm());
    const childSectionForm = reactive(createEmptyQuestDescriptionSections());
    const childQuests = ref<ChildQuestCreateDraft[]>([]);
    const isAddingChildQuest = ref(initialType === 'personal');
    const isSubmitting = ref(false);
    const errorMessage = ref('');
    const fieldErrors = reactive<Record<string, string>>({});

    const clearErrors = (): void => {
        errorMessage.value = '';
        for (const key of Object.keys(fieldErrors)) {
            delete fieldErrors[key];
        }
    };

    const resetChildQuestDraft = (): void => {
        Object.assign(childQuestForm, createEmptyChildQuestForm());
        Object.assign(childSectionForm, createEmptyQuestDescriptionSections());
    };

    const resetChildQuestForm = (): void => {
        resetChildQuestDraft();
        childQuests.value = [];
        isAddingChildQuest.value = createType.value === 'personal';
    };

    const resetForms = (): void => {
        Object.assign(unitForm, createEmptyUnitForm());
        Object.assign(questForm, createEmptyQuestForm(
            createType.value === 'special' ? 'special' : 'team',
        ));
        Object.assign(sectionForm, createEmptyQuestDescriptionSections());
        resetChildQuestForm();
        clearErrors();
    };

    const setCreateType = (type: MentorQuestCreateType): void => {
        createType.value = type;
        clearErrors();
        resetChildQuestForm();

        if (type === 'team' || type === 'special') {
            Object.assign(questForm, createEmptyQuestForm(type));
        }
    };

    const startAddingChildQuest = (): void => {
        clearErrors();
        isAddingChildQuest.value = true;
    };

    const appendCurrentChildQuest = (): boolean => {
        clearErrors();

        if (!childQuestForm.title.trim()) {
            errorMessage.value = 'クエストタイトルを入力してください。';
            return false;
        }

        childQuests.value.push({
            title: childQuestForm.title.trim(),
            toolIds: [...childQuestForm.toolIds],
            difficulty: childQuestForm.difficulty,
            questTier: childQuestForm.questTier,
            skillGrants: [...childQuestForm.skillGrants],
            sections: copySections(childSectionForm),
            sortOrder: childQuests.value.length + 1,
        });
        resetChildQuestDraft();
        isAddingChildQuest.value = true;

        return true;
    };

    const removeChildQuest = (index: number): void => {
        childQuests.value = renumberChildQuests(
            childQuests.value.filter((_, questIndex) => questIndex !== index),
        );
    };

    const currentChildQuestNo = computed(() => childQuests.value.length + 1);

    const collectChildQuestDrafts = (): ChildQuestCreateDraft[] => {
        const drafts = renumberChildQuests([...childQuests.value]);

        if (childQuestForm.title.trim()) {
            drafts.push({
                title: childQuestForm.title.trim(),
                toolIds: [...childQuestForm.toolIds],
                difficulty: childQuestForm.difficulty,
                questTier: childQuestForm.questTier,
                skillGrants: [...childQuestForm.skillGrants],
                sections: copySections(childSectionForm),
                sortOrder: drafts.length + 1,
            });
        }

        return drafts;
    };

    const validatePersonalCreate = (): boolean => {
        clearErrors();

        if (!unitForm.title.trim()) {
            errorMessage.value = 'ユニットタイトルを入力してください。';
            return false;
        }

        const drafts = collectChildQuestDrafts();

        if (drafts.length === 0) {
            errorMessage.value = 'クエストを1件以上追加してください。';
            return false;
        }

        return true;
    };

    const createPersonalUnit = async (): Promise<MentorQuestUnitItem | null> => {
        if (isSubmitting.value || !validatePersonalCreate()) {
            return null;
        }

        isSubmitting.value = true;

        try {
            const drafts = collectChildQuestDrafts();

            const unit = await createMentorQuestUnit({
                title: unitForm.title.trim(),
                quests: drafts.map((draft) => buildChildQuestPayload(draft)),
            });

            return unit;
        } catch (error: unknown) {
            Object.assign(fieldErrors, extractApiFieldErrors(error, [
                ...UNIT_FIELDS,
                ...CHILD_QUEST_FIELDS,
            ]));
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                mentorQuestAdminMessages.createUnitFailed,
            );

            return null;
        } finally {
            isSubmitting.value = false;
        }
    };

    const submit = async (): Promise<boolean> => {
        if (isSubmitting.value || createType.value === 'personal') {
            return false;
        }

        isSubmitting.value = true;
        clearErrors();

        try {
            const serialized = serializeQuestDescriptionSections(
                sectionForm as QuestDescriptionSections,
            );

            await createMentorQuest({
                type: questForm.type,
                title: questForm.title.trim(),
                description: serialized.description,
                clearCondition: serialized.clearCondition,
                isRequired: questForm.isRequired,
                unlockLevel: questForm.unlockLevel ? Number(questForm.unlockLevel) : null,
                rewardText: questForm.rewardText.trim(),
                badgeLabel: questForm.badgeLabel.trim(),
                difficulty: questForm.difficulty ?? null,
                skillGrants: [...questForm.skillGrants],
                toolIds: [...(questForm.toolIds ?? [])],
            });

            resetForms();
            return true;
        } catch (error: unknown) {
            Object.assign(fieldErrors, extractApiFieldErrors(error, QUEST_FIELDS));
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                mentorQuestAdminMessages.createQuestFailed,
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
        sectionForm,
        childQuestForm,
        childSectionForm,
        childQuests,
        isAddingChildQuest,
        isSubmitting,
        errorMessage,
        fieldErrors,
        setCreateType,
        startAddingChildQuest,
        appendCurrentChildQuest,
        removeChildQuest,
        currentChildQuestNo,
        validatePersonalCreate,
        createPersonalUnit,
        submit,
        resetForms,
    };
}
