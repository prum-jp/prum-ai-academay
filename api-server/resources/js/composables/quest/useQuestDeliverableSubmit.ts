import { computed, ref, watch, type Ref } from 'vue';
import {
    deleteQuestSubmissionImage,
    submitQuestSubmission,
} from '@/api/quest/quests';
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import {
    DEFAULT_QUEST_SUBMISSION_TYPE,
    isFileSubmissionType,
    questSubmissionMaxImages,
    questSubmissionMessages,
    type QuestSubmissionType,
    validateSubmissionFile,
} from '@/constants/quest/questSubmission';
import type { QuestItem } from '@/types/quest/quest';
import type { QuestSubmission } from '@/types/quest/questSubmission';

const isValidUrl = (value: string): boolean => {
    try {
        const parsed = new URL(value);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch {
        return false;
    }
};

export function useQuestDeliverableSubmit(options: {
    questId: number;
    submission: Ref<QuestSubmission | null>;
    isLocked: Ref<boolean>;
    onSaved: (quest: QuestItem) => void;
}) {
    const selectedType = ref<QuestSubmissionType>(
        options.submission.value?.type ?? DEFAULT_QUEST_SUBMISSION_TYPE,
    );
    const linkInput = ref(
        options.submission.value?.type === 'link' ? options.submission.value.url ?? '' : '',
    );
    const textInput = ref(
        options.submission.value?.type === 'text' ? options.submission.value.text ?? '' : '',
    );
    const selectedFile = ref<File | null>(null);
    const selectedFileName = ref('');
    const savedSubmission = ref<QuestSubmission | null>(options.submission.value);
    const isSubmitting = ref(false);
    const isDeletingImage = ref(false);
    const deletingImageId = ref<number | null>(null);
    const errorMessage = ref('');
    const successMessage = ref('');

    const savedImages = computed(() => savedSubmission.value?.files ?? []);
    const isImageLimitReached = computed(
        () => selectedType.value === 'image' && savedImages.value.length >= questSubmissionMaxImages,
    );
    const isBusy = computed(() => isSubmitting.value || isDeletingImage.value);

    const submitButtonLabel = computed((): string => {
        if (selectedType.value === 'image' && savedImages.value.length > 0) {
            return questSubmissionMessages.addImageLabel;
        }

        return questSheetConfig.deliverable.submitLabel;
    });

    const canSubmit = computed((): boolean => {
        if (selectedType.value === 'link') {
            return linkInput.value.trim() !== '';
        }

        if (selectedType.value === 'text') {
            return textInput.value.trim() !== '';
        }

        if (isImageLimitReached.value) {
            return false;
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

    const clearFileInput = (): void => {
        selectedFile.value = null;
        selectedFileName.value = '';
    };

    watch(options.submission, (nextSubmission) => {
        applySubmissionToForm(nextSubmission);
    });

    const clearMessages = (): void => {
        errorMessage.value = '';
        successMessage.value = '';
    };

    const onTypeChange = (): void => {
        clearMessages();
        clearFileInput();
    };

    const onFileChange = (file: File | null): void => {
        selectedFile.value = file;
        selectedFileName.value = file?.name ?? '';
        clearMessages();
    };

    const handleSavedQuest = (updated: QuestItem, successText: string): void => {
        applySubmissionToForm(updated.submission ?? null);
        successMessage.value = successText;
        clearFileInput();
        options.onSaved(updated);
    };

    const validateBeforeSubmit = (): boolean => {
        if (selectedType.value === 'link') {
            if (!isValidUrl(linkInput.value.trim())) {
                errorMessage.value = questSubmissionMessages.invalidUrl;
                return false;
            }
        } else if (selectedType.value === 'text') {
            if (textInput.value.trim() === '') {
                errorMessage.value = questSubmissionMessages.emptyText;
                return false;
            }
        } else if (isFileSubmissionType(selectedType.value)) {
            if (!selectedFile.value) {
                errorMessage.value = questSubmissionMessages.emptyFile;
                return false;
            }

            const fileError = validateSubmissionFile(selectedType.value, selectedFile.value);
            if (fileError) {
                errorMessage.value = fileError;
                return false;
            }
        }

        return true;
    };

    const submit = async (): Promise<void> => {
        clearMessages();

        if (!validateBeforeSubmit()) {
            return;
        }

        isSubmitting.value = true;

        try {
            const updated = await submitQuestSubmission(options.questId, {
                type: selectedType.value,
                url: selectedType.value === 'link' ? linkInput.value.trim() : undefined,
                text: selectedType.value === 'text' ? textInput.value.trim() : undefined,
                file: isFileSubmissionType(selectedType.value)
                    ? selectedFile.value ?? undefined
                    : undefined,
            });

            const successText = selectedType.value === 'image'
                ? questSubmissionMessages.addImageSuccess
                : questSubmissionMessages.submitSuccess;

            handleSavedQuest(updated, successText);
        } catch {
            errorMessage.value = questSubmissionMessages.submitFailed;
        } finally {
            isSubmitting.value = false;
        }
    };

    const deleteImage = async (fileId: number): Promise<void> => {
        clearMessages();
        isDeletingImage.value = true;
        deletingImageId.value = fileId;

        try {
            const updated = await deleteQuestSubmissionImage(options.questId, fileId);
            handleSavedQuest(updated, questSubmissionMessages.deleteImageSuccess);
        } catch {
            errorMessage.value = questSubmissionMessages.deleteImageFailed;
        } finally {
            isDeletingImage.value = false;
            deletingImageId.value = null;
        }
    };

    return {
        selectedType,
        linkInput,
        textInput,
        selectedFileName,
        savedSubmission,
        isSubmitting,
        isDeletingImage,
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
    };
}
