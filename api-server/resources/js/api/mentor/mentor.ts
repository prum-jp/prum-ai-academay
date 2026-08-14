import axios from 'axios';
import type { MentorStudent, MentorStudentListResponse } from '@/types/mentor/mentor';

export const fetchMentorStudents = async (
    page = 1,
    query = '',
): Promise<MentorStudentListResponse> => {
    const { data } = await axios.get<MentorStudentListResponse>('/api/mentor/students', {
        params: {
            page,
            ...(query.trim() !== '' ? { q: query.trim() } : {}),
        },
    });

    return data;
};

export const selectMentorStudent = async (studentId: number): Promise<MentorStudent> => {
    const { data } = await axios.put<{ data: MentorStudent }>('/api/mentor/target-student', {
        studentId,
    });

    return data.data;
};

export interface CreateMentorStudentPayload {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role: number;
}

export const createMentorStudent = async (
    payload: CreateMentorStudentPayload,
): Promise<MentorStudent> => {
    const { data } = await axios.post<{ data: MentorStudent }>('/api/mentor/students', payload);

    return data.data;
};

export const deleteMentorStudent = async (studentId: number): Promise<void> => {
    await axios.delete(`/api/mentor/students/${studentId}`);
};
