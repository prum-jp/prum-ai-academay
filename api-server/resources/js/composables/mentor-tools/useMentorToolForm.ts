import { reactive, ref } from 'vue';
import { createMentorTool, updateMentorTool } from '@/api/mentor-tools/toolAdmin';
import type { MentorTool } from '@/types/mentor-quest/questAdmin';
import { mentorToolMessages } from '@/constants/mentor-tools/toolAdmin';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';
import { extractApiFieldErrors } from '@/utils/shared/extractApiFieldErrors';

const FORM_FIELDS = ['name'] as const;

interface ToolForm {
    name: string;
}

const createEmptyForm = (): ToolForm => ({
    name: '',
});

export function useMentorToolForm(editingTool: () => MentorTool | null) {
    const form = reactive(createEmptyForm());
    const isSubmitting = ref(false);
    const errorMessage = ref('');
    const fieldErrors = reactive<Record<string, string>>({});

    const clearErrors = (): void => {
        errorMessage.value = '';
        for (const field of FORM_FIELDS) {
            delete fieldErrors[field];
        }
    };

    const resetForm = (): void => {
        const tool = editingTool();
        form.name = tool?.name ?? '';
        clearErrors();
    };

    const submit = async (): Promise<MentorTool | null> => {
        if (isSubmitting.value) {
            return null;
        }

        isSubmitting.value = true;
        clearErrors();

        const payload = {
            name: form.name.trim(),
        };

        try {
            const tool = editingTool();
            const saved = tool
                ? await updateMentorTool(tool.id, payload)
                : await createMentorTool(payload);

            resetForm();
            return saved;
        } catch (error: unknown) {
            Object.assign(fieldErrors, extractApiFieldErrors(error, FORM_FIELDS));
            const hasFieldErrors = FORM_FIELDS.some((field) => fieldErrors[field]);
            errorMessage.value = hasFieldErrors
                ? ''
                : extractApiErrorMessage(
                      error,
                      undefined,
                      editingTool()
                          ? mentorToolMessages.updateFailed
                          : mentorToolMessages.createFailed,
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
