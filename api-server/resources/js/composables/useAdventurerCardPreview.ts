import { ref, watch, type Ref } from 'vue';
import type { AdventurerProfile } from '@/types/adventurer';
import { formatAdventurerCard } from '@/utils/formatAdventurerCard';

interface AdventurerCardPreviewOptions {
    toastDurationMs?: number;
}

export function useAdventurerCardPreview(
    profile: Ref<AdventurerProfile | null>,
    options: AdventurerCardPreviewOptions = {},
) {
    const slackPreview = ref('');
    const showToast = ref(false);
    const toastDurationMs = options.toastDurationMs ?? 2500;

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
            showToast.value = true;
            window.setTimeout(() => {
                showToast.value = false;
            }, toastDurationMs);
        } catch (error) {
            console.error('Copy failed:', error);
        }
    };

    return {
        slackPreview,
        showToast,
        copyAdventurerCard,
    };
}
