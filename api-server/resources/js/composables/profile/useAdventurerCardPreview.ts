import { ref, watch, type Ref } from 'vue';
import type { AdventurerProfile } from '@/types/profile/adventurer';
import { studentDirectoryMessages } from '@/constants/student/studentDirectory';
import { useToastNotice } from '@/composables/shared/useToastNotice';
import { formatAdventurerCard } from '@/utils/profile/formatAdventurerCard';

interface AdventurerCardPreviewOptions {
    toastDurationMs?: number;
}

export function useAdventurerCardPreview(
    profile: Ref<AdventurerProfile | null>,
    options: AdventurerCardPreviewOptions = {},
) {
    const slackPreview = ref('');
    const toastDurationMs = options.toastDurationMs ?? 2500;
    const { showToast, toastMessage, showNotice } = useToastNotice(toastDurationMs);

    const refreshSlackPreview = (): void => {
        if (!profile.value) {
            slackPreview.value = '';
            return;
        }

        slackPreview.value = formatAdventurerCard(profile.value);
    };

    watch(
        profile,
        () => {
            refreshSlackPreview();
        },
        { deep: true, immediate: true },
    );

    const copyAdventurerCard = async (): Promise<void> => {
        if (!slackPreview.value) {
            return;
        }

        try {
            await navigator.clipboard.writeText(slackPreview.value);
            showNotice(studentDirectoryMessages.copyAdventurerCardSuccessToast);
        } catch (error) {
            console.error('Copy failed:', error);
        }
    };

    return {
        slackPreview,
        showToast,
        toastMessage,
        copyAdventurerCard,
    };
}
