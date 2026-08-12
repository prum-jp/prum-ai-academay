import { reactive, ref } from 'vue';
import { createMentorStudent, type CreateMentorStudentPayload } from '@/api/mentor/mentor';
import type { MentorStudent } from '@/types/mentor/mentor';
import { ROLE_STUDENT } from '@/types/shared/auth';
import { mentorStudentMessages } from '@/constants/mentor/mentor';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';
import { extractApiFieldErrors } from '@/utils/shared/extractApiFieldErrors';

const REGISTER_FIELDS = [
    'name',
    'email',
    'password',
    'password_confirmation',
    'role',
] as const;

const createEmptyForm = (): CreateMentorStudentPayload => ({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: ROLE_STUDENT,
});

export function useMentorStudentRegister() {
    const form = reactive(createEmptyForm());
    const isSubmitting = ref(false);
    const errorMessage = ref('');
    const fieldErrors = reactive<Record<string, string>>({});

    const clearErrors = (): void => {
        errorMessage.value = '';
        for (const field of REGISTER_FIELDS) {
            delete fieldErrors[field];
        }
    };

    const resetForm = (): void => {
        Object.assign(form, createEmptyForm());
        clearErrors();
    };

    const submit = async (): Promise<MentorStudent | null> => {
        if (isSubmitting.value) {
            return null;
        }

        isSubmitting.value = true;
        clearErrors();

        try {
            const student = await createMentorStudent({
                name: form.name.trim(),
                email: form.email.trim(),
                password: form.password,
                password_confirmation: form.password_confirmation,
                role: Number(form.role),
            });
            resetForm();
            return student;
        } catch (error: unknown) {
            Object.assign(fieldErrors, extractApiFieldErrors(error, REGISTER_FIELDS));
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                mentorStudentMessages.registerFailed,
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
