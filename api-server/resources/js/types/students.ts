import type { StudentListItem } from '@/types/studentList';
import type { PaginatedResponse, PaginationMeta } from '@/types/pagination';

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
