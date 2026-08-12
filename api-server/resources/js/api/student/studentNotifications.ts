import axios from 'axios';
import type {
    StudentNotificationItem,
    StudentNotificationListResponse,
} from '@/types/student/studentNotification';

export const fetchStudentNotifications = async (): Promise<StudentNotificationListResponse> => {
    const { data } = await axios.get<StudentNotificationListResponse>('/api/notifications');

    return data;
};

export const deleteStudentNotification = async (notificationId: number): Promise<void> => {
    await axios.delete(`/api/notifications/${notificationId}`);
};
