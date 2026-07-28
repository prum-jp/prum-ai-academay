import { reactive, ref } from 'vue';
import { createMentorTool } from '@/api/toolAdmin';
import type { MentorTool } from '@/types/questAdmin';
import { mentorToolMessages } from '@/constants/toolAdmin';
import { useGameAudio } from '@/composables/useGameAudio';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';
import { extractApiFieldErrors } from '@/utils/extractApiFieldErrors';

const CREATE_FIELDS = ['code', 'name'] as const;

interface ToolCreateForm {
    code: string;
    name: string;
}

const createEmptyForm = (): ToolCreateForm => ({
    code: '',
    name: '',
});

export function useMentorToolCreate() {
    const form = reactive(createEmptyForm());
    const isSubmitting = ref(false);
    const errorMessage = ref('');
    const fieldErrors = reactive<Record<string, string>>({});

    const { playSound } = useGameAudio();

    const clearErrors = (): void => {
        errorMessage.value = '';
        for (const field of CREATE_FIELDS) {
            delete fieldErrors[field];
        }
    };

    const resetForm = (): void => {
        Object.assign(form, createEmptyForm());
        clearErrors();
    };

    const submit = async (): Promise<MentorTool | null> => {
        if (isSubmitting.value) {
            return null;
        }

        isSubmitting.value = true;
        clearErrors();

        try {
            const tool = await createMentorTool({
                code: form.code.trim(),
                name: form.name.trim(),
            });
            playSound('level-up');
            resetForm();
            return tool;
        } catch (error: unknown) {
            playSound('down');
            Object.assign(fieldErrors, extractApiFieldErrors(error, CREATE_FIELDS));
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                mentorToolMessages.createFailed,
            );
            return null;
        } finally {
            isSubmitting.value = false;
        }
    };

    return {
        form,
        isSubmitting,
        errorMessage,
        fieldErrors,
        submit,
        resetForm,
    };
}
