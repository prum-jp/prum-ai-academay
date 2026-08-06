import { ref } from 'vue';
import { fetchMentorReviewRequests } from '@/api/mentorNotifications';
import type { MentorReviewRequestItem } from '@/types/mentorNotifications';
import { mentorNotificationsConfig } from '@/constants/mentorNotifications';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

const items = ref<MentorReviewRequestItem[]>([]);
const total = ref(0);
const isLoading = ref(false);
const error = ref('');
let requestId = 0;

export function useMentorReviewRequests() {
    const refresh = async (): Promise<void> => {
        const currentRequestId = ++requestId;
        isLoading.value = true;
        error.value = '';

        try {
            const response = await fetchMentorReviewRequests();
            if (currentRequestId !== requestId) {
                return;
            }

            items.value = response.data;
            total.value = response.meta.total;
        } catch (caughtError: unknown) {
            if (currentRequestId !== requestId) {
                return;
            }

            error.value = extractApiErrorMessage(
                caughtError,
                undefined,
                mentorNotificationsConfig.loadFailed,
            );
            items.value = [];
            total.value = 0;
        } finally {
            if (currentRequestId === requestId) {
                isLoading.value = false;
            }
        }
    };

    return {
        items,
        total,
        isLoading,
        error,
        refresh,
    };
}
