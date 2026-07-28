<template>
    <RpgModal
        :open="open"
        :title="questImportModalConfig.title"
        :icon="questImportModalConfig.icon"
        wide
        @close="handleClose"
    >
        <div v-if="step === 'upload'" class="quest-import-upload">
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
                <MentorPublishToggle
                    :model-value="allPublished"
                    :on-label="questImportModalConfig.publishAllOnLabel"
                    :off-label="questImportModalConfig.publishAllOffLabel"
                    :disabled="isLoading || isApplying || items.length === 0"
                    @update:model-value="setAllPublished"
                />
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
                        @sync-unit-publish="syncUnitPublish"
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
                    :disabled="isLoading || isApplying"
                    @click="backToUpload"
                >
                    {{ questImportModalConfig.backLabel }}
                </RpgButton>
                <RpgButton
                    v-else
                    type="button"
                    :disabled="isLoading || isApplying"
                    @click="handleClose"
                >
                    {{ questImportModalConfig.cancelLabel }}
                </RpgButton>

                <RpgButton
                    v-if="step === 'preview'"
                    type="button"
                    icon="fa-solid fa-check"
                    :disabled="isLoading || isApplying || hasBlockingErrors || items.length === 0"
                    @click="onApply"
                >
                    {{
                        isApplying
                            ? questImportModalConfig.applyingLabel
                            : questImportModalConfig.applyLabel
                    }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import { questImportMessages, questImportModalConfig } from '@/constants/questImport';
import { useMentorQuestImport } from '@/composables/useMentorQuestImport';
import { buildPreviewGroups } from '@/utils/questImport/previewGroups';
import MentorPublishToggle from '@/components/rpg/MentorPublishToggle.vue';
import MentorQuestImportPreviewRow from '@/components/rpg/MentorQuestImportPreviewRow.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import RpgModal from '@/components/rpg/RpgModal.vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
    imported: [];
}>();

const {
    step,
    items,
    meta,
    parseErrors,
    errorMessage,
    isLoading,
    isApplying,
    selectedFileName,
    hasBlockingErrors,
    reset,
    loadCsvFile,
    removeItem,
    setAllPublished,
    syncUnitPublish,
    apply,
} = useMentorQuestImport();

const allPublished = computed(
    (): boolean => items.value.length > 0 && items.value.every((item) => item.isPublished),
);

const previewGroups = computed(() => buildPreviewGroups(items.value));
watch(
    () => props.open,
    (open) => {
        if (!open) {
            reset();
        }
    },
);

const handleClose = (): void => {
    if (isApplying.value) {
        return;
    }

    reset();
    emit('close');
};

const backToUpload = (): void => {
    reset();
};

const onFileChange = async (event: Event): Promise<void> => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) {
        return;
    }

    await loadCsvFile(file);
    input.value = '';
};

const onApply = async (): Promise<void> => {
    const success = await apply();
    if (!success) {
        return;
    }

    emit('imported');
    reset();
    emit('close');
};
</script>
