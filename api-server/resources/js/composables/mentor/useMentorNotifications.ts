import { ref } from 'vue';
import {
    deleteMentorNotification,
    fetchMentorNotifications,
} from '@/api/mentor/mentorNotification';
import { mentorNotificationConfig } from '@/constants/mentor/mentorNotification';
import type { MentorNotificationItem } from '@/types/mentor/mentorNotification';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';

const items = ref<MentorNotificationItem[]>([]);
const total = ref(0);
const isLoading = ref(false);
const error = ref('');
let requestId = 0;

export function useMentorNotifications() {
    const refresh = async (): Promise<void> => {
        const currentRequestId = ++requestId;
        isLoading.value = true;
        error.value = '';

        try {
            const response = await fetchMentorNotifications();
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
                mentorNotificationConfig.loadFailed,
            );
            items.value = [];
            total.value = 0;
        } finally {
            if (currentRequestId === requestId) {
                isLoading.value = false;
            }
        }
    };

    const removeNotification = async (notificationId: number): Promise<boolean> => {
        try {
            await deleteMentorNotification(notificationId);
            items.value = items.value.filter((item) => item.id !== notificationId);
            total.value = items.value.length;
            return true;
        } catch {
            error.value = mentorNotificationConfig.deleteFailed;
            return false;
        }
    };

    return {
        items,
        total,
        isLoading,
        error,
        refresh,
        removeNotification,
    };
}
