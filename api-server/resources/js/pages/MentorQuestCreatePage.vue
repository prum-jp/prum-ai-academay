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

                <section
                    v-if="childQuests.length > 0"
                    class="mentor-quest-create-added-list"
                >
                    <h3 class="mentor-quest-create-section-title">
                        {{ mentorQuestCreatePageConfig.addedChildQuestsTitle }}
                        ({{ childQuests.length }})
                    </h3>
                    <ul class="mentor-quest-create-added-items">
                        <li
                            v-for="(quest, index) in childQuests"
                            :key="`${quest.title}-${index}`"
                            class="mentor-quest-create-added-item"
                        >
                            <span class="mentor-quest-create-added-index">{{ quest.sortOrder }}</span>
                            <span class="mentor-quest-create-added-title">{{ quest.title }}</span>
                            <button
                                type="button"
                                class="mentor-quest-create-added-remove"
                                :disabled="isSubmitting"
                                @click="removeChildQuest(index)"
                            >
                                {{ mentorChildQuestFormLabels.remove }}
                            </button>
                        </li>
                    </ul>
                </section>

                <section
                    v-if="isAddingChildQuest"
                    class="mentor-quest-create-sheet-form mentor-quest-create-child-quest"
                >
                    <h3 class="mentor-quest-create-section-title">
                        {{ mentorQuestCreatePageConfig.childQuestSectionTitle }}
                    </h3>

                    <QuestSheetLayout :quest-no="currentChildQuestNo">
                        <template #title>
                            <input
                                id="child-quest-create-title"
                                v-model="childQuestForm.title"
                                class="quest-sheet-create-meta-input quest-sheet-heading-title-input"
                                type="text"
                                required
                                maxlength="255"
                                :placeholder="mentorQuestFormPlaceholders.childQuestTitle"
                                :disabled="isSubmitting"
                            />
                        </template>

                        <template #quest-status>
                            <span class="quest-sheet-create-type-badge">
                                {{ mentorPersonalAssignmentSectionConfig.childQuestTypeLabel }}
                            </span>
                        </template>

                        <template #meta>
                            <QuestSheetCreateChildMetaFields
                                v-model:tool-ids="childQuestForm.toolIds"
                                v-model:difficulty="childQuestForm.difficulty"
                                v-model:quest-tier="childQuestForm.questTier"
                                v-model:skill-grants="childQuestForm.skillGrants"
                                :tools="tools"
                                :disabled="isSubmitting"
                            />
                        </template>

                        <QuestSheetCreateSections
                            v-model="childSectionForm"
                            :disabled="isSubmitting"
                        />
                    </QuestSheetLayout>
                </section>

                <p v-if="fieldErrors.title" class="login-error">{{ fieldErrors.title }}</p>
                <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

                <div v-if="isAddingChildQuest" class="mentor-quest-create-page-actions">
                    <RpgButton
                        type="button"
                        tone="orange"
                        icon="fa-solid fa-plus"
                        :disabled="isSubmitting"
                        @click="onAppendChildQuest"
                    >
                        {{ mentorQuestCreatePageConfig.addChildQuestLabel }}
                    </RpgButton>
                    <RpgButton
                        type="submit"
                        tone="green"
                        icon="fa-solid fa-floppy-disk"
                        :disabled="isSubmitting"
                    >
                        {{
                            isSubmitting
                                ? mentorQuestCreatePageConfig.submittingLabel
                                : mentorQuestCreatePageConfig.submitLabel
                        }}
                    </RpgButton>
                    <RpgButton
                        type="button"
                        tone="red"
                        :disabled="isSubmitting"
                        @click="onCancel"
                    >
                        {{ mentorQuestCreatePageConfig.cancelLabel }}
                    </RpgButton>
                </div>

                <div v-else class="mentor-quest-create-page-actions">
                    <RpgButton
                        type="button"
                        tone="orange"
                        icon="fa-solid fa-plus"
                        :disabled="isSubmitting"
                        @click="onStartAddingChildQuest"
                    >
                        {{ mentorQuestCreatePageConfig.addChildQuestLabel }}
                    </RpgButton>
                </div>
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

                <div class="mentor-quest-create-page-actions">
                    <RpgButton
                        type="submit"
                        icon="fa-solid fa-plus"
                        :disabled="isSubmitting"
                    >
                        {{
                            isSubmitting
                                ? mentorQuestCreatePageConfig.submittingLabel
                                : mentorQuestCreatePageConfig.submitLabel
                        }}
                    </RpgButton>
                    <RpgButton
                        type="button"
                        tone="red"
                        :disabled="isSubmitting"
                        @click="onCancel"
                    >
                        {{ mentorQuestCreatePageConfig.cancelLabel }}
                    </RpgButton>
                </div>
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
import { assignMentorQuestUnitToAllStudents } from '@/api/curriculum';
import { fetchMentorTools } from '@/api/questAdmin';
import { assignStudentQuestUnit } from '@/api/questUnitAssignment';
import {
    mentorChildQuestFormLabels,
    mentorPersonalAssignmentSectionConfig,
    mentorQuestCreatePageConfig,
    mentorQuestCreateTypeOptions,
    mentorQuestFormPlaceholders,
} from '@/constants/questAdmin';
import { mentorAssignmentMessages } from '@/constants/curriculum';
import { questImportModalConfig } from '@/constants/questImport';
import type { CurriculumAssignmentTarget } from '@/types/curriculum';
import type { MentorQuestCreateType, MentorTool } from '@/types/questAdmin';
import { useMentorQuestCreate } from '@/composables/useMentorQuestCreate';
import MentorQuestCreateTypeField from '@/components/rpg/MentorQuestCreateTypeField.vue';
import MentorQuestBulkImportModal from '@/components/rpg/MentorQuestBulkImportModal.vue';
import MentorQuestUnitAssignModal from '@/components/rpg/MentorQuestUnitAssignModal.vue';
import MentorUnitFormFields from '@/components/rpg/MentorUnitFormFields.vue';
import QuestSheetCreateChildMetaFields from '@/components/rpg/QuestSheetCreateChildMetaFields.vue';
import QuestSheetCreateMetaFields from '@/components/rpg/QuestSheetCreateMetaFields.vue';
import QuestSheetCreateSections from '@/components/rpg/QuestSheetCreateSections.vue';
import QuestSheetLayout from '@/components/rpg/QuestSheetLayout.vue';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';

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
