import axios from 'axios';
import type { MentorStudentQuestUnitAssignmentData } from '@/types/curriculum';

export const fetchStudentQuestUnitAssignments = async (
    studentId: number,
): Promise<MentorStudentQuestUnitAssignmentData> => {
    const { data } = await axios.get<{ data: MentorStudentQuestUnitAssignmentData }>(
        `/api/mentor/students/${studentId}/quest-units`,
    );

    return data.data;
};

export const assignStudentQuestUnit = async (
    studentId: number,
    questUnitId: number,
): Promise<MentorStudentQuestUnitAssignmentData> => {
    const { data } = await axios.post<{ data: MentorStudentQuestUnitAssignmentData }>(
        `/api/mentor/students/${studentId}/quest-units/${questUnitId}/assign`,
    );

    return data.data;
};

export const unassignStudentQuestUnit = async (
    studentId: number,
    questUnitId: number,
): Promise<MentorStudentQuestUnitAssignmentData> => {
    const { data } = await axios.delete<{ data: MentorStudentQuestUnitAssignmentData }>(
        `/api/mentor/students/${studentId}/quest-units/${questUnitId}/assign`,
    );

    return data.data;
};

export const assignStudentQuest = async (
    studentId: number,
    questId: number,
): Promise<MentorStudentQuestUnitAssignmentData> => {
    const { data } = await axios.post<{ data: MentorStudentQuestUnitAssignmentData }>(
        `/api/mentor/students/${studentId}/quests/${questId}/assign`,
    );

    return data.data;
};

export const unassignStudentQuest = async (
    studentId: number,
    questId: number,
): Promise<MentorStudentQuestUnitAssignmentData> => {
    const { data } = await axios.delete<{ data: MentorStudentQuestUnitAssignmentData }>(
        `/api/mentor/students/${studentId}/quests/${questId}/assign`,
    );

    return data.data;
};
