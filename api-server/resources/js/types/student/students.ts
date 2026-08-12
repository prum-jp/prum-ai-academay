import type { StudentListItem } from '@/types/student/studentList';
import type { PaginatedResponse, PaginationMeta } from '@/types/shared/pagination';

export interface StudentDirectoryNeighbor {
    id: number;
    name: string;
}

export interface PeerStudentProfileResponse {
    navigation: {
        next: StudentDirectoryNeighbor | null;
    };
}

export type StudentDirectoryListResponse = PaginatedResponse<StudentListItem>;

export type { PaginationMeta as StudentDirectoryListMeta };
