export interface MentorStudent {
    id: number;
    name: string;
    email: string;
    avatarUrl: string | null;
    levelTitle: string;
    earnedBadgeCount: number;
    isSelected: boolean;
}

export interface MentorStudentListMeta {
    selectedStudentId: number | null;
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
}

export interface MentorStudentListResponse {
    data: MentorStudent[];
    meta: MentorStudentListMeta;
}
