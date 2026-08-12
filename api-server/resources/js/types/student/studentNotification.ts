export type StudentNotificationType = 'curriculum_added' | 'status_changed' | 'comment';

export interface StudentNotificationItem {
    id: number;
    type: StudentNotificationType;
    message: string;
    questId: number | null;
    curriculumId: number | null;
    createdAt: string;
}

export interface StudentNotificationListResponse {
    data: StudentNotificationItem[];
    meta: {
        total: number;
    };
}
