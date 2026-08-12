<template>
    <div class="quest-sheet-deliverable">
        <div class="quest-sheet-deliverable-type-field">
            <p class="quest-sheet-deliverable-type-label">{{ questSubmissionMessages.typeLabel }}</p>
            <QuestSubmissionTypePicker
                v-model="selectedType"
                :disabled="isLocked || isSubmitting"
                @update:model-value="onTypeChange"
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
                :disabled="isLocked || isSubmitting"
                @change="onFileChange"
            />
            <p class="quest-sheet-deliverable-file-note">
                {{ fileHint }}
            </p>
            <p v-if="selectedFileName" class="quest-sheet-deliverable-file-name">
                {{ selectedFileName }}
            </p>
        </div>

        <button
            type="button"
            class="btn-rpg quest-sheet-deliverable-submit"
            :disabled="isLocked || isSubmitting || !canSubmit"
            @click="onSubmit"
        >
            <i v-if="isSubmitting" class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
            {{
                isSubmitting
                    ? questSheetConfig.deliverable.submittingLabel
                    : questSheetConfig.deliverable.submitLabel
            }}
        </button>

        <p v-if="errorMessage" class="quest-sheet-deliverable-error">{{ errorMessage }}</p>
        <p v-else-if="successMessage" class="quest-sheet-deliverable-success">
            {{ successMessage }}
        </p>

        <div v-if="savedSubmission" class="quest-sheet-deliverable-preview">
            <p class="quest-sheet-deliverable-saved">
                <span class="quest-sheet-deliverable-saved-label">
                    {{ questSubmissionMessages.savedLabel }}
                </span>
                <span class="quest-sheet-deliverable-saved-type">
                    （{{ questSubmissionTypeLabels[savedSubmission.type] }}）
                </span>
            </p>

            <a
                v-if="savedSubmission.type === 'link' && savedSubmission.url"
                class="quest-sheet-deliverable-link"
                :href="savedSubmission.url"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{ questSubmissionMessages.openLinkLabel }}
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>

            <a
                v-else-if="savedSubmission.url && savedSubmission.type !== 'text'"
                class="quest-sheet-deliverable-link"
                :href="savedSubmission.url"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{ questSubmissionMessages.openFileLabel }}
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>

            <img
                v-if="savedSubmission.type === 'image' && savedSubmission.url"
                class="quest-sheet-deliverable-image"
                :src="savedSubmission.url"
                :alt="questSubmissionMessages.previewImage"
            />

            <video
                v-if="savedSubmission.type === 'video' && savedSubmission.url"
                class="quest-sheet-deliverable-media"
                :src="savedSubmission.url"
                controls
            />

            <audio
                v-if="savedSubmission.type === 'audio' && savedSubmission.url"
                class="quest-sheet-deliverable-media"
                :src="savedSubmission.url"
                controls
            />

            <p
                v-if="savedSubmission.type === 'text' && savedSubmission.text"
                class="quest-sheet-deliverable-text"
            >
                {{ savedSubmission.text }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, useId, watch } from 'vue';
import { submitQuestSubmission } from '@/api/quest/quests';
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import {
    DEFAULT_QUEST_SUBMISSION_TYPE,
    isFileSubmissionType,
    questSubmissionAcceptByType,
    questSubmissionMaxSizeLabels,
    questSubmissionMessages,
    questSubmissionTypeLabels,
    type QuestSubmissionType,
    validateSubmissionFile,
} from '@/constants/quest/questSubmission';
import type { QuestItem } from '@/types/quest/quest';
import type { QuestSubmission } from '@/types/quest/questSubmission';
import QuestSubmissionTypePicker from '@/components/rpg/quest-sheet/QuestSubmissionTypePicker.vue';

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

const selectedType = ref<QuestSubmissionType>(
    props.submission?.type ?? DEFAULT_QUEST_SUBMISSION_TYPE,
);
const linkInput = ref(props.submission?.type === 'link' ? props.submission.url ?? '' : '');
const textInput = ref(props.submission?.type === 'text' ? props.submission.text ?? '' : '');
const selectedFile = ref<File | null>(null);
const selectedFileName = ref('');
const savedSubmission = ref<QuestSubmission | null>(props.submission);
const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

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

const canSubmit = computed((): boolean => {
    if (selectedType.value === 'link') {
        return linkInput.value.trim() !== '';
    }

    if (selectedType.value === 'text') {
        return textInput.value.trim() !== '';
    }

    return selectedFile.value !== null;
});

const applySubmissionToForm = (submission: QuestSubmission | null): void => {
    savedSubmission.value = submission;

    if (!submission) {
        selectedType.value = DEFAULT_QUEST_SUBMISSION_TYPE;
        linkInput.value = '';
        textInput.value = '';
        selectedFile.value = null;
        selectedFileName.value = '';
        return;
    }

    selectedType.value = submission.type;
    linkInput.value = submission.type === 'link' ? submission.url ?? '' : '';
    textInput.value = submission.type === 'text' ? submission.text ?? '' : '';
    selectedFile.value = null;
    selectedFileName.value = '';
};

watch(
    () => props.submission,
    (nextSubmission) => {
        applySubmissionToForm(nextSubmission);
    },
);

const onTypeChange = (): void => {
    errorMessage.value = '';
    successMessage.value = '';
    selectedFile.value = null;
    selectedFileName.value = '';
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const onFileChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    selectedFile.value = file;
    selectedFileName.value = file?.name ?? '';
    errorMessage.value = '';
    successMessage.value = '';
};

const isValidUrl = (value: string): boolean => {
    try {
        const parsed = new URL(value);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch {
        return false;
    }
};

const onSubmit = async (): Promise<void> => {
    errorMessage.value = '';
    successMessage.value = '';

    if (selectedType.value === 'link') {
        const trimmed = linkInput.value.trim();
        if (!isValidUrl(trimmed)) {
            errorMessage.value = questSubmissionMessages.invalidUrl;
            return;
        }
    } else if (selectedType.value === 'text') {
        if (textInput.value.trim() === '') {
            errorMessage.value = questSubmissionMessages.emptyText;
            return;
        }
    } else if (isFileSubmissionType(selectedType.value)) {
        if (!selectedFile.value) {
            errorMessage.value = questSubmissionMessages.emptyFile;
            return;
        }

        const fileError = validateSubmissionFile(selectedType.value, selectedFile.value);
        if (fileError) {
            errorMessage.value = fileError;
            return;
        }
    }

    isSubmitting.value = true;

    try {
        const updated = await submitQuestSubmission(props.questId, {
            type: selectedType.value,
            url: selectedType.value === 'link' ? linkInput.value.trim() : undefined,
            text: selectedType.value === 'text' ? textInput.value.trim() : undefined,
            file: isFileSubmissionType(selectedType.value) ? selectedFile.value ?? undefined : undefined,
        });

        applySubmissionToForm(updated.submission ?? null);
        successMessage.value = questSubmissionMessages.submitSuccess;
        emit('saved', updated);
    } catch {
        errorMessage.value = questSubmissionMessages.submitFailed;
    } finally {
        isSubmitting.value = false;
    }
};
</script>
