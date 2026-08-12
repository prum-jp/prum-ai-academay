<template>
    <MentorPanel :config="mentorQuestCreatePageConfig">
        <div class="quest-sheet-page mentor-quest-create-page">
            <p class="quest-sheet-back">
                <RouterLink class="quest-sheet-back-link" :to="{ name: 'mentor-quests' }">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    {{ mentorQuestCreatePageConfig.backLabel }}
                </RouterLink>
            </p>

            <div class="mentor-quest-create-toolbar">
                <MentorQuestCreateTypeField
                    :model-value="createType"
                    :disabled="isSubmitting"
                    @update:model-value="onCreateTypeChange"
                />

                <button
                    type="button"
                    class="mentor-register-link"
                    @click="isBulkImportOpen = true"
                >
                    <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
                    {{ questImportModalConfig.bulkButtonLabel }}
                </button>
            </div>

            <form
                v-if="createType === 'personal'"
                class="mentor-quest-create-form"
                @submit.prevent="onPersonalSubmitRequest"
            >
                <MentorUnitFormFields
                    :form="unitForm"
                    :field-errors="fieldErrors"
                    id-prefix="unit"
                    :disabled="isSubmitting"
                    :placeholders="{
                        title: mentorQuestFormPlaceholders.unitTitle,
                    }"
                />

                <MentorChildQuestList
                    :items="childQuestListItems"
                    :title="mentorQuestCreatePageConfig.addedChildQuestsTitle"
                    removable
                    :disabled="isSubmitting"
                    :remove-label="mentorChildQuestFormLabels.remove"
                    @remove="removeChildQuest"
                />

                <QuestSheetPersonalQuestForm
                    v-if="isAddingChildQuest"
                    v-model:title="childQuestForm.title"
                    v-model:sections="childSectionForm"
                    v-model:tool-ids="childQuestForm.toolIds"
                    v-model:difficulty="childQuestForm.difficulty"
                    v-model:quest-tier="childQuestForm.questTier"
                    v-model:skill-grants="childQuestForm.skillGrants"
                    :quest-no="currentChildQuestNo"
                    :section-title="mentorQuestCreatePageConfig.childQuestSectionTitle"
                    :tools="tools"
                    :disabled="isSubmitting"
                    editable-meta
                    title-id="child-quest-create-title"
                    :title-placeholder="mentorQuestFormPlaceholders.childQuestTitle"
                    title-required
                />

                <p v-if="fieldErrors.title" class="login-error">{{ fieldErrors.title }}</p>
                <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

                <MentorFormSubmitBar
                    v-if="isAddingChildQuest"
                    :is-submitting="isSubmitting"
                    :submit-label="mentorQuestCreatePageConfig.submitLabel"
                    :submitting-label="mentorQuestCreatePageConfig.submittingLabel"
                    submit-tone="green"
                    show-cancel
                    :cancel-label="mentorQuestCreatePageConfig.cancelLabel"
                    @cancel="onCancel"
                >
                    <RpgButton
                        type="button"
                        tone="orange"
                        icon="fa-solid fa-plus"
                        :disabled="isSubmitting"
                        @click="onAppendChildQuest"
                    >
                        {{ mentorQuestCreatePageConfig.addChildQuestLabel }}
                    </RpgButton>
                </MentorFormSubmitBar>

                <MentorFormSubmitBar
                    v-else
                    :show-submit="false"
                    :is-submitting="isSubmitting"
                >
                    <RpgButton
                        type="button"
                        tone="orange"
                        icon="fa-solid fa-plus"
                        :disabled="isSubmitting"
                        @click="onStartAddingChildQuest"
                    >
                        {{ mentorQuestCreatePageConfig.addChildQuestLabel }}
                    </RpgButton>
                </MentorFormSubmitBar>
            </form>

            <form v-else class="mentor-quest-create-sheet-form" @submit.prevent="onSubmit">
                <QuestSheetLayout :quest-no="null">
                    <template #title>
                        <input
                            id="quest-create-title"
                            v-model="questForm.title"
                            class="quest-sheet-create-meta-input quest-sheet-heading-title-input"
                            type="text"
                            required
                            maxlength="255"
                            :placeholder="mentorQuestFormPlaceholders.questTitle"
                            :disabled="isSubmitting"
                        />
                    </template>

                    <template #quest-status>
                        <span class="quest-sheet-create-type-badge">
                            {{ createTypeLabel }}
                        </span>
                    </template>

                    <template #meta>
                        <QuestSheetCreateMetaFields
                            :form="questForm"
                            v-model:skill-grants="questForm.skillGrants"
                            v-model:tool-ids="questForm.toolIds"
                            :tools="tools"
                            show-skill-grants
                            :disabled="isSubmitting"
                        />
                    </template>

                    <QuestSheetCreateSections
                        v-model="sectionForm"
                        :disabled="isSubmitting"
                    />
                </QuestSheetLayout>

                <p v-if="fieldErrors.title" class="login-error">{{ fieldErrors.title }}</p>
                <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

                <MentorFormSubmitBar
                    :is-submitting="isSubmitting"
                    :submit-label="mentorQuestCreatePageConfig.submitLabel"
                    :submitting-label="mentorQuestCreatePageConfig.submittingLabel"
                    submit-icon="fa-solid fa-plus"
                    show-cancel
                    :cancel-label="mentorQuestCreatePageConfig.cancelLabel"
                    @cancel="onCancel"
                />
            </form>
        </div>

        <MentorQuestBulkImportModal
            :open="isBulkImportOpen"
            @close="isBulkImportOpen = false"
            @imported="onImported"
        />

        <MentorQuestUnitAssignModal
            :open="isAssignModalOpen"
            :is-submitting="isAssignSubmitting"
            :error-message="assignErrorMessage"
            @close="closeAssignModal"
            @confirm="onConfirmAssignment"
        />
    </MentorPanel>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { assignMentorQuestUnitToAllStudents } from '@/api/mentor-quest/curriculum';
import { fetchMentorTools } from '@/api/mentor-quest/questAdmin';
import { assignStudentQuestUnit } from '@/api/mentor-quest/questUnitAssignment';
import {
    mentorChildQuestFormLabels,
    mentorQuestCreatePageConfig,
    mentorQuestCreateTypeOptions,
    mentorQuestFormPlaceholders,
} from '@/constants/mentor-quest/questAdmin';
import { mentorAssignmentMessages } from '@/constants/mentor-quest/curriculum';
import { questImportModalConfig } from '@/constants/mentor-quest/questImport';
import type { CurriculumAssignmentTarget } from '@/types/mentor-quest/curriculum';
import type { MentorQuestCreateType, MentorTool } from '@/types/mentor-quest/questAdmin';
import { useMentorQuestCreate } from '@/composables/mentor-quest/useMentorQuestCreate';
import MentorQuestCreateTypeField from '@/components/rpg/mentor-quest/MentorQuestCreateTypeField.vue';
import MentorQuestBulkImportModal from '@/components/rpg/mentor-quest/MentorQuestBulkImportModal.vue';
import MentorQuestUnitAssignModal from '@/components/rpg/mentor-quest/MentorQuestUnitAssignModal.vue';
import MentorUnitFormFields from '@/components/rpg/mentor-quest/MentorUnitFormFields.vue';
import QuestSheetCreateMetaFields from '@/components/rpg/quest-sheet/QuestSheetCreateMetaFields.vue';
import QuestSheetCreateSections from '@/components/rpg/quest-sheet/QuestSheetCreateSections.vue';
import QuestSheetLayout from '@/components/rpg/quest-sheet/QuestSheetLayout.vue';
import QuestSheetPersonalQuestForm from '@/components/rpg/quest-sheet/QuestSheetPersonalQuestForm.vue';
import MentorChildQuestList from '@/components/rpg/mentor-quest/MentorChildQuestList.vue';
import MentorFormSubmitBar from '@/components/rpg/mentor-quest/MentorFormSubmitBar.vue';
import MentorPanel from '@/components/rpg/mentor/MentorPanel.vue';
import RpgButton from '@/components/rpg/shared/RpgButton.vue';

const route = useRoute();
const router = useRouter();
const isBulkImportOpen = ref(false);
const isAssignModalOpen = ref(false);
const isAssignSubmitting = ref(false);
const assignErrorMessage = ref('');
const pendingCreatedUnitId = ref<number | null>(null);
const tools = ref<MentorTool[]>([]);

const resolveInitialType = (): MentorQuestCreateType => {
    const value = route.query.type;
    if (value === 'personal' || value === 'team' || value === 'special') {
        return value;
    }

    return 'team';
};

const {
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
} = useMentorQuestCreate(resolveInitialType());

const createTypeLabel = computed((): string => {
    const option = mentorQuestCreateTypeOptions.find((item) => item.value === createType.value);

    return option?.label ?? createType.value;
});

const childQuestListItems = computed(() =>
    childQuests.value.map((quest) => ({
        sortOrder: quest.sortOrder,
        title: quest.title,
    })),
);

watch(
    () => route.query.type,
    (value) => {
        if (value === 'personal' || value === 'team' || value === 'special') {
            setCreateType(value);
        }
    },
);

watch(createType, () => {
    if (tools.value.length === 0) {
        void loadTools();
    }
});

const onCreateTypeChange = (value: MentorQuestCreateType): void => {
    setCreateType(value);

    void router.replace({
        name: 'mentor-quest-create',
        query: value === 'team' ? {} : { type: value },
    });
};

const onCancel = async (): Promise<void> => {
    await router.push({ name: 'mentor-quests' });
};

const loadTools = async (): Promise<void> => {
    try {
        tools.value = await fetchMentorTools();
    } catch {
        tools.value = [];
    }
};

const onStartAddingChildQuest = async (): Promise<void> => {
    if (tools.value.length === 0) {
        await loadTools();
    }

    startAddingChildQuest();
};

const onAppendChildQuest = async (): Promise<void> => {
    if (tools.value.length === 0) {
        await loadTools();
    }

    appendCurrentChildQuest();
};

const onPersonalSubmitRequest = (): void => {
    if (!validatePersonalCreate()) {
        return;
    }

    assignErrorMessage.value = '';
    isAssignModalOpen.value = true;
};

const closeAssignModal = (): void => {
    if (isAssignSubmitting.value) {
        return;
    }

    isAssignModalOpen.value = false;
    assignErrorMessage.value = '';
    pendingCreatedUnitId.value = null;
};

const onConfirmAssignment = async (payload: {
    assignmentTarget: CurriculumAssignmentTarget;
    studentIds: number[];
}): Promise<void> => {
    if (isAssignSubmitting.value) {
        return;
    }

    isAssignSubmitting.value = true;
    assignErrorMessage.value = '';

    let unitId = pendingCreatedUnitId.value;

    if (unitId === null) {
        const unit = await createPersonalUnit();
        if (!unit) {
            isAssignSubmitting.value = false;
            isAssignModalOpen.value = false;

            return;
        }

        unitId = unit.id;
        pendingCreatedUnitId.value = unitId;
    }

    try {
        if (payload.assignmentTarget === 'all') {
            await assignMentorQuestUnitToAllStudents(unitId);
        } else {
            await Promise.all(
                payload.studentIds.map((studentId) =>
                    assignStudentQuestUnit(studentId, unitId),
                ),
            );
        }

        isAssignModalOpen.value = false;
        pendingCreatedUnitId.value = null;
        resetForms();
        await router.push({
            name: 'mentor-quests',
            query: { notice: 'unit-created' },
        });
    } catch {
        assignErrorMessage.value =
            payload.assignmentTarget === 'all'
                ? mentorAssignmentMessages.assignAllStudentsFailed
                : mentorAssignmentMessages.assignSelectedStudentsFailed;
    } finally {
        isAssignSubmitting.value = false;
    }
};

const onSubmit = async (): Promise<void> => {
    const created = await submit();
    if (!created) {
        return;
    }

    await router.push({
        name: 'mentor-quests',
        query: {
            notice: createType.value === 'personal' ? 'unit-created' : 'quest-created',
        },
    });
};

const onImported = async (): Promise<void> => {
    isBulkImportOpen.value = false;
    await router.push({
        name: 'mentor-quests',
        query: { notice: 'imported' },
    });
};

onMounted(() => {
    void loadTools();
});
</script>
