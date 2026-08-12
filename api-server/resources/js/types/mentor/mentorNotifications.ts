export interface MentorReviewRequestItem {
    studentId: number;
    studentName: string;
    questId: number;
    questTitle: string;
    type: 'review_requested';
    typeLabel: string;
    requestedAt: string;
}

export interface MentorReviewRequestListResponse {
    data: MentorReviewRequestItem[];
    meta: {
        total: number;
    };
}
