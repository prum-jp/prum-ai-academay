import { ref } from 'vue';
import { fetchMentorReviewRequests } from '@/api/mentor/mentorNotifications';
import type { MentorReviewRequestItem } from '@/types/mentor/mentorNotifications';
import { mentorReviewRequestsConfig } from '@/constants/mentor/mentorReviewRequests';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';

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
                mentorReviewRequestsConfig.loadFailed,
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
