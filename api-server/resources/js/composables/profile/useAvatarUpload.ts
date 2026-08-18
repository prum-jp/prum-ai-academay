import { ref } from 'vue';
import type { AdventurerProfile } from '@/types/profile/adventurer';
import { deleteStudentAvatar, uploadStudentAvatar } from '@/api/profile/profile';
import { useAuth } from '@/composables/shared/useAuth';
import { avatarMessages } from '@/constants/profile/avatar';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';

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
            const profile = await uploadStudentAvatar(file);
            onUpdated(profile);

            const hint = profile.avatarUrlError?.hint ?? profile.avatarUrlError?.message;
            if (hint) {
                error.value = hint;
            }
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
