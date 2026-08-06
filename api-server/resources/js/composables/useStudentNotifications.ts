import { ref } from 'vue';
import {
    fetchStudentNotifications,
    markStudentNotificationAsRead,
} from '@/api/studentNotifications';
import { studentNotificationsConfig } from '@/constants/studentNotifications';
import type { StudentNotificationItem } from '@/types/studentNotification';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

const items = ref<StudentNotificationItem[]>([]);
const total = ref(0);
const isLoading = ref(false);
const error = ref('');
let requestId = 0;

export function useStudentNotifications() {
    const refresh = async (): Promise<void> => {
        const currentRequestId = ++requestId;
        isLoading.value = true;
        error.value = '';

        try {
            const response = await fetchStudentNotifications();
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
                studentNotificationsConfig.loadFailed,
            );
            items.value = [];
            total.value = 0;
        } finally {
            if (currentRequestId === requestId) {
                isLoading.value = false;
            }
        }
    };

    const markAsRead = async (notificationId: number): Promise<boolean> => {
        try {
            await markStudentNotificationAsRead(notificationId);
            items.value = items.value.filter((item) => item.id !== notificationId);
            total.value = items.value.length;
            return true;
        } catch {
            error.value = studentNotificationsConfig.markReadFailed;
            return false;
        }
    };

    return {
        items,
        total,
        isLoading,
        error,
        refresh,
        markAsRead,
    };
}
