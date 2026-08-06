import { ref } from 'vue';
import type { AdventurerProfile } from '@/types/adventurer';
import { deleteStudentAvatar, uploadStudentAvatar } from '@/api/profile';
import { useAuth } from '@/composables/useAuth';
import { avatarMessages } from '@/constants/avatar';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

export function useAvatarUpload(onUpdated: (profile: AdventurerProfile) => void) {
    const error = ref('');
    const isUpdating = ref(false);

    const { isStudent } = useAuth();

    const setError = (message: string): void => {
        error.value = message;
    };

    const clearError = (): void => {
        error.value = '';
    };

    const upload = async (file: File): Promise<void> => {
        if (!isStudent.value || isUpdating.value) {
            return;
        }

        isUpdating.value = true;
        clearError();

        try {
            onUpdated(await uploadStudentAvatar(file));
        } catch (caughtError: unknown) {
            error.value = extractApiErrorMessage(
                caughtError,
                'avatar',
                avatarMessages.uploadFailed,
            );
        } finally {
            isUpdating.value = false;
        }
    };

    const reset = async (hasAvatar: boolean): Promise<void> => {
        if (!isStudent.value || isUpdating.value || !hasAvatar) {
            return;
        }

        isUpdating.value = true;
        clearError();

        try {
            onUpdated(await deleteStudentAvatar());
        } catch (caughtError: unknown) {
            error.value = extractApiErrorMessage(
                caughtError,
                'avatar',
                avatarMessages.resetFailed,
            );
        } finally {
            isUpdating.value = false;
        }
    };

    return {
        error,
        isUpdating,
        setError,
        upload,
        reset,
    };
}
