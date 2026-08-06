<template>
    <div class="mentor-quest-bulk-import">
        <RpgModal
        :open="open"
        :title="questImportModalConfig.title"
        :icon="questImportModalConfig.icon"
        wide
        @close="handleClose"
    >
        <div v-if="step === 'upload'" class="quest-import-upload">
            <div class="input-group">
                <label for="quest-import-default-tier">
                    {{ questImportModalConfig.defaultQuestTierLabel }}
                </label>
                <select
                    id="quest-import-default-tier"
                    v-model="defaultQuestTier"
                    class="quest-sheet-create-meta-input"
                    :disabled="isLoading"
                >
                    <option
                        v-for="option in QUEST_TIER_OPTIONS"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}（{{ option.requirement }}）
                    </option>
                </select>
            </div>
            <p class="mentor-register-note">{{ questImportModalConfig.defaultQuestTierHint }}</p>

            <div class="input-group">
                <label for="quest-import-file">{{ questImportModalConfig.uploadLabel }}</label>
                <input
                    id="quest-import-file"
                    type="file"
                    accept=".csv,text/csv"
                    :disabled="isLoading"
                    @change="onFileChange"
                />
            </div>
            <p class="mentor-register-note">{{ questImportModalConfig.uploadHint }}</p>
            <p v-if="selectedFileName" class="quest-import-file-name">{{ selectedFileName }}</p>
        </div>

        <div v-else class="quest-import-preview">
            <div v-if="meta" class="quest-import-summary-bar">
                <p class="quest-import-summary">
                    {{ questImportMessages.summary(meta) }}
                </p>
            </div>

            <p v-if="isLoading" class="async-state-note">{{ questImportModalConfig.previewLoadingLabel }}</p>

            <div v-else class="quest-import-preview-list">
                <div
                    v-for="(group, groupIndex) in previewGroups"
                    :key="group.key"
                    class="quest-import-unit-group"
                >
                    <div
                        v-if="groupIndex > 0"
                        class="quest-import-unit-divider"
                        role="separator"
                        aria-hidden="true"
                    >
                        <span class="quest-import-unit-divider-rule"></span>
                    </div>

                    <MentorQuestImportPreviewRow
                        v-for="entry in group.entries"
                        :key="entry.item.clientId"
                        v-model="items[entry.index]"
                        :all-items="items"
                        @remove="removeItem(entry.item.clientId)"
                    />
                </div>
            </div>
        </div>

        <ul v-if="parseErrors.length > 0" class="quest-import-error-list">
            <li v-for="(message, index) in parseErrors" :key="index">{{ message }}</li>
        </ul>

        <p v-if="step === 'preview' && hasBlockingErrors && !isLoading" class="login-error">
            {{ questImportMessages.hasErrors }}
        </p>

        <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

        <template #footer>
            <div class="mentor-quest-create-actions">
                <RpgButton
                    v-if="step === 'preview'"
                    type="button"
                    :disabled="isLoading || isApplying || isAssignSubmitting"
                    @click="backToUpload"
                >
                    {{ questImportModalConfig.backLabel }}
                </RpgButton>
                <RpgButton
                    v-else
                    type="button"
                    :disabled="isLoading || isApplying || isAssignSubmitting"
                    @click="handleClose"
                >
                    {{ questImportModalConfig.cancelLabel }}
                </RpgButton>

                <RpgButton
                    v-if="step === 'preview'"
                    type="button"
                    icon="fa-solid fa-users"
                    :disabled="isLoading || isApplying || isAssignSubmitting || hasBlockingErrors || items.length === 0"
                    @click="openAssignModal"
                >
                    {{
                        isApplying || isAssignSubmitting
                            ? questImportModalConfig.assigningLabel
                            : questImportModalConfig.assignLabel
                    }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>

    <MentorQuestUnitAssignModal
        :open="isAssignModalOpen"
        :is-submitting="isAssignSubmitting"
        :error-message="assignErrorMessage"
        :description="questImportModalConfig.assignModalDescription"
        :submit-label="questImportModalConfig.assignLabel"
        :submitting-label="questImportModalConfig.assigningLabel"
        @close="closeAssignModal"
        @confirm="onConfirmAssignment"
    />
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { questImportMessages, questImportModalConfig } from '@/constants/questImport';
import { QUEST_TIER_OPTIONS } from '@/constants/questTier';
import { mentorAssignmentMessages } from '@/constants/curriculum';
import type { CurriculumAssignmentTarget } from '@/types/curriculum';
import { useMentorQuestImport } from '@/composables/useMentorQuestImport';
import { assignQuestUnitsToStudents } from '@/utils/assignQuestUnitsToStudents';
import { buildPreviewGroups } from '@/utils/questImport/previewGroups';
import { collectPersonalUnitIdsFromImportResults } from '@/utils/questImport/collectPersonalUnitIds';
import MentorQuestImportPreviewRow from '@/components/rpg/MentorQuestImportPreviewRow.vue';
import MentorQuestUnitAssignModal from '@/components/rpg/MentorQuestUnitAssignModal.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import RpgModal from '@/components/rpg/RpgModal.vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
    imported: [];
}>();

const isAssignModalOpen = ref(false);
const isAssignSubmitting = ref(false);
const assignErrorMessage = ref('');

const {
    step,
    items,
    meta,
    parseErrors,
    errorMessage,
    isLoading,
    isApplying,
    selectedFileName,
    defaultQuestTier,
    hasBlockingErrors,
    reset,
    loadCsvFile,
    removeItem,
    apply,
} = useMentorQuestImport();

const previewGroups = computed(() => buildPreviewGroups(items.value));

watch(
    () => props.open,
    (open) => {
        if (!open) {
            reset();
            closeAssignModal();
        }
    },
);

const handleClose = (): void => {
    if (isApplying.value || isAssignSubmitting.value) {
        return;
    }

    reset();
    closeAssignModal();
    emit('close');
};

const backToUpload = (): void => {
    closeAssignModal();
    reset();
};

const onFileChange = async (event: Event): Promise<void> => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) {
        return;
    }

    await loadCsvFile(file, defaultQuestTier.value);
    input.value = '';
};

const openAssignModal = (): void => {
    if (hasBlockingErrors.value || items.value.length === 0) {
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

    const results = await apply();
    if (!results) {
        isAssignSubmitting.value = false;
        isAssignModalOpen.value = false;

        return;
    }

    const unitIds = collectPersonalUnitIdsFromImportResults(results);

    if (unitIds.length === 0) {
        isAssignModalOpen.value = false;
        isAssignSubmitting.value = false;
        emit('imported');
        reset();
        emit('close');
        return;
    }

    try {
        await assignQuestUnitsToStudents(
            unitIds,
            payload.assignmentTarget,
            payload.studentIds,
        );

        isAssignModalOpen.value = false;
        emit('imported');
        reset();
        emit('close');
    } catch {
        assignErrorMessage.value =
            payload.assignmentTarget === 'all'
                ? mentorAssignmentMessages.assignAllStudentsFailed
                : mentorAssignmentMessages.assignSelectedStudentsFailed;
    } finally {
        isAssignSubmitting.value = false;
    }
};
</script>
