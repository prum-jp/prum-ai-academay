import axios from 'axios';
import type {
    StudentNotificationItem,
    StudentNotificationListResponse,
} from '@/types/studentNotification';

export const fetchStudentNotifications = async (): Promise<StudentNotificationListResponse> => {
    const { data } = await axios.get<StudentNotificationListResponse>('/api/notifications');

    return data;
};

export const markStudentNotificationAsRead = async (
    notificationId: number,
): Promise<StudentNotificationItem> => {
    const { data } = await axios.patch<{ data: StudentNotificationItem }>(
        `/api/notifications/${notificationId}/read`,
    );

    return data.data;
};
