import axios from 'axios';
import type { AdventurerProfile } from '@/types/adventurer';
import type { PeerStudentProfileResponse, StudentDirectoryListResponse } from '@/types/students';

export const fetchStudentDirectory = async (
    page = 1,
    query = '',
): Promise<StudentDirectoryListResponse> => {
    const { data } = await axios.get<StudentDirectoryListResponse>('/api/students', {
        params: {
            page,
            ...(query.trim() !== '' ? { q: query.trim() } : {}),
        },
    });

    return data;
};

export const fetchPeerStudentProfile = async (
    studentId: number,
): Promise<AdventurerProfile & PeerStudentProfileResponse> => {
    const { data } = await axios.get<AdventurerProfile & PeerStudentProfileResponse>(
        `/api/students/${studentId}`,
    );

    return data;
};
