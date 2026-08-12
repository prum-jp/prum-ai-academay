import { useRouter } from 'vue-router';
import { mentorStudentMessages } from '@/constants/mentor/mentor';
import { useToastNotice } from '@/composables/shared/useToastNotice';

const REGISTER_SUCCESS_TOAST_MS = 900;

export function useMentorRegisterPage() {
    const router = useRouter();
    const { showToast, toastMessage, showNotice } = useToastNotice(REGISTER_SUCCESS_TOAST_MS);

    const handleCreated = (): void => {
        showNotice(mentorStudentMessages.registerSuccessToast, {
            onDismiss: () => {
                void router.push({ name: 'mentor-quests' });
            },
        });
    };

    return {
        showToast,
        toastMessage,
        handleCreated,
    };
}
