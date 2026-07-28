import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { mentorStudentMessages } from '@/constants/mentor';

export function useMentorRegisterPage() {
    const router = useRouter();
    const showToast = ref(false);

    const handleCreated = (): void => {
        showToast.value = true;
        window.setTimeout(() => {
            showToast.value = false;
            void router.push({ name: 'mentor' });
        }, 900);
    };

    return {
        showToast,
        toastMessage: mentorStudentMessages.registerSuccessToast,
        handleCreated,
    };
}
