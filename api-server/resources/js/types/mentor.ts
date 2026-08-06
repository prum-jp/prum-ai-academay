import type { PaginationMeta } from '@/types/pagination';

export interface MentorStudent {
    id: number;
    name: string;
    email: string;
    avatarUrl: string | null;
    levelTitle: string;
    earnedBadgeCount: number;
    isSelected: boolean;
}

export interface MentorStudentListMeta extends PaginationMeta {
    selectedStudentId: number | null;
}

export interface MentorStudentListResponse {
    data: MentorStudent[];
    meta: MentorStudentListMeta;
}
