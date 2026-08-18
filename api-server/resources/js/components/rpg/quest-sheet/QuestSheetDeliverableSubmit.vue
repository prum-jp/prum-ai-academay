<template>
    <div class="quest-sheet-deliverable">
        <div class="quest-sheet-deliverable-type-field">
            <p class="quest-sheet-deliverable-type-label">{{ questSubmissionMessages.typeLabel }}</p>
            <QuestSubmissionTypePicker
                v-model="selectedType"
                :disabled="isLocked || isBusy"
                @update:model-value="handleTypeChange"
            />
        </div>

        <div v-if="selectedType === 'link'" class="input-group quest-sheet-deliverable-input">
            <label :for="linkInputId">{{ questSubmissionMessages.linkLabel }}</label>
            <input
                :id="linkInputId"
                v-model="linkInput"
                type="url"
                inputmode="url"
                autocomplete="url"
                :placeholder="questSheetConfig.deliverable.urlPlaceholder"
                :disabled="isLocked || isSubmitting"
            />
        </div>

        <div v-else-if="selectedType === 'text'" class="input-group quest-sheet-deliverable-input">
            <label :for="textInputId">{{ questSubmissionMessages.textLabel }}</label>
            <textarea
                :id="textInputId"
                v-model="textInput"
                rows="5"
                maxlength="10000"
                :placeholder="questSheetConfig.deliverable.textPlaceholder"
                :disabled="isLocked || isSubmitting"
            />
        </div>

        <div v-else class="input-group quest-sheet-deliverable-input">
            <label :for="fileInputId">{{ questSubmissionMessages.fileLabel }}</label>
            <input
                :id="fileInputId"
                ref="fileInputRef"
                type="file"
                :accept="fileAccept"
                :disabled="isLocked || isBusy || isImageLimitReached"
                @change="onFileInputChange"
            />
            <p class="quest-sheet-deliverable-file-note">
                {{ fileHint }}
            </p>
            <p v-if="selectedFileName" class="quest-sheet-deliverable-file-name">
                {{ selectedFileName }}
            </p>
            <p v-if="selectedType === 'image' && isImageLimitReached" class="quest-sheet-deliverable-file-note">
                {{ questSubmissionMessages.maxImagesReached }}
            </p>
        </div>

        <button
            type="button"
            class="btn-rpg quest-sheet-deliverable-submit"
            :disabled="isLocked || isBusy || !canSubmit"
            @click="submit"
        >
            <i v-if="isSubmitting" class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
            {{
                isSubmitting
                    ? questSheetConfig.deliverable.submittingLabel
                    : submitButtonLabel
            }}
        </button>

        <p v-if="errorMessage" class="quest-sheet-deliverable-error">{{ errorMessage }}</p>
        <p v-else-if="successMessage" class="quest-sheet-deliverable-success">
            {{ successMessage }}
        </p>

        <QuestSheetSubmissionPreview
            :submission="savedSubmission"
            :is-locked="isLocked"
            :is-busy="isBusy"
            :deleting-image-id="deletingImageId"
            @delete-image="deleteImage"
        />
    </div>
</template>

<script setup lang="ts">
import { computed, ref, toRef, useId } from 'vue';
import QuestSheetSubmissionPreview from '@/components/rpg/quest-sheet/QuestSheetSubmissionPreview.vue';
import QuestSubmissionTypePicker from '@/components/rpg/quest-sheet/QuestSubmissionTypePicker.vue';
import { useQuestDeliverableSubmit } from '@/composables/quest/useQuestDeliverableSubmit';
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import {
    isFileSubmissionType,
    questSubmissionAcceptByType,
    questSubmissionMaxSizeLabels,
    questSubmissionMessages,
} from '@/constants/quest/questSubmission';
import type { QuestItem } from '@/types/quest/quest';
import type { QuestSubmission } from '@/types/quest/questSubmission';

const props = defineProps<{
    questId: number;
    submission: QuestSubmission | null;
    isLocked: boolean;
}>();

const emit = defineEmits<{
    saved: [quest: QuestItem];
}>();

const linkInputId = useId();
const textInputId = useId();
const fileInputId = useId();
const fileInputRef = ref<HTMLInputElement | null>(null);

const {
    selectedType,
    linkInput,
    textInput,
    selectedFileName,
    savedSubmission,
    isSubmitting,
    deletingImageId,
    isBusy,
    errorMessage,
    successMessage,
    isImageLimitReached,
    submitButtonLabel,
    canSubmit,
    onTypeChange,
    onFileChange,
    submit,
    deleteImage,
} = useQuestDeliverableSubmit({
    questId: props.questId,
    submission: toRef(props, 'submission'),
    isLocked: toRef(props, 'isLocked'),
    onSaved: (quest) => emit('saved', quest),
});

const fileAccept = computed(() =>
    isFileSubmissionType(selectedType.value)
        ? questSubmissionAcceptByType[selectedType.value]
        : '',
);

const fileHint = computed(() =>
    isFileSubmissionType(selectedType.value)
        ? `※${questSubmissionMaxSizeLabels[selectedType.value]}以下`
        : '',
);

const onFileInputChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    onFileChange(input.files?.[0] ?? null);
};

const handleTypeChange = (): void => {
    onTypeChange();
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};
</script>
