export type MentorNotificationType = 'comment';

export interface MentorNotificationItem {
    id: number;
    type: MentorNotificationType;
    message: string;
    studentId: number | null;
    questId: number | null;
    createdAt: string;
}

export interface MentorNotificationListResponse {
    data: MentorNotificationItem[];
    meta: {
        total: number;
    };
}
