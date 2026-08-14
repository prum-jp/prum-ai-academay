import axios from 'axios';
import type { MentorNotificationListResponse } from '@/types/mentor/mentorNotification';

export const fetchMentorNotifications = async (): Promise<MentorNotificationListResponse> => {
    const { data } = await axios.get<MentorNotificationListResponse>('/api/mentor/notifications');

    return data;
};

export const deleteMentorNotification = async (notificationId: number): Promise<void> => {
    await axios.delete(`/api/mentor/notifications/${notificationId}`);
};
