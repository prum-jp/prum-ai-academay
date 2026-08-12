import axios from 'axios';
import type {
    CreateMentorCurriculumPayload,
    MentorCurriculumDetail,
    MentorCurriculumItem,
    MentorCurriculumListResponse,
    MentorStudentAssignmentData,
    MentorStudentPickerListResponse,
    UpdateMentorCurriculumPayload,
    UpdateMentorStudentAssignmentsPayload,
} from '@/types/mentor-quest/curriculum';

export const fetchMentorStudentAssignments = async (
    studentId: number,
): Promise<MentorStudentAssignmentData> => {
    const { data } = await axios.get<{ data: MentorStudentAssignmentData }>(
        `/api/mentor/students/${studentId}/assignments`,
    );

    return data.data;
};

export const updateMentorStudentAssignments = async (
    studentId: number,
    payload: UpdateMentorStudentAssignmentsPayload,
): Promise<MentorStudentAssignmentData> => {
    const { data } = await axios.put<{ data: MentorStudentAssignmentData }>(
        `/api/mentor/students/${studentId}/assignments`,
        payload,
    );

    return data.data;
};

export const fetchMentorStudentPicker = async (
    page = 1,
    query = '',
): Promise<MentorStudentPickerListResponse> => {
    const { data } = await axios.get<MentorStudentPickerListResponse>(
        '/api/mentor/students/picker',
        {
            params: {
                page,
                ...(query.trim() !== '' ? { q: query.trim() } : {}),
            },
        },
    );

    return data;
};

export const fetchMentorCurricula = async (page = 1): Promise<MentorCurriculumListResponse> => {
    const { data } = await axios.get<MentorCurriculumListResponse>('/api/mentor/curricula', {
        params: { page },
    });

    return data;
};

export const fetchMentorCurriculumDetail = async (id: number): Promise<MentorCurriculumDetail> => {
    const { data } = await axios.get<{ data: MentorCurriculumDetail }>(
        `/api/mentor/curricula/${id}`,
    );

    return data.data;
};

export const createMentorCurriculum = async (
    payload: CreateMentorCurriculumPayload,
): Promise<MentorCurriculumItem> => {
    const { data } = await axios.post<{ data: MentorCurriculumItem }>(
        '/api/mentor/curricula',
        payload,
    );

    return data.data;
};

export const updateMentorCurriculum = async (
    id: number,
    payload: UpdateMentorCurriculumPayload,
): Promise<MentorCurriculumItem> => {
    const { data } = await axios.put<{ data: MentorCurriculumItem }>(
        `/api/mentor/curricula/${id}`,
        payload,
    );

    return data.data;
};

export const deleteMentorCurriculum = async (id: number): Promise<void> => {
    await axios.delete(`/api/mentor/curricula/${id}`);
};

export const assignMentorCurriculumToAllStudents = async (
    id: number,
): Promise<number> => {
    const { data } = await axios.post<{ data: { assignedCount: number } }>(
        `/api/mentor/curricula/${id}/assign-all-students`,
    );

    return data.data.assignedCount;
};

export const assignMentorQuestUnitToAllStudents = async (id: number): Promise<number> => {
    const { data } = await axios.post<{ data: { assignedCount: number } }>(
        `/api/mentor/quest-units/${id}/assign-all-students`,
    );

    return data.data.assignedCount;
};
