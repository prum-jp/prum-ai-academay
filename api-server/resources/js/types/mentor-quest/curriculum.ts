export interface MentorCurriculumItem {
    id: number;
    name: string;
    description: string | null;
    sortOrder: number;
    unitCount: number;
}

export type CurriculumAssignmentTarget = 'all' | 'selected';

export interface MentorStudentQuestAssignmentStatus {
    assigned: boolean;
    directlyAssigned: boolean;
    canUnassign: boolean;
}

export interface MentorStudentQuestUnitChildQuestItem extends MentorStudentQuestAssignmentStatus {
    id: number;
    title: string;
    viaCurriculum: boolean;
}

export interface MentorStudentQuestUnitAssignmentItem extends MentorStudentQuestAssignmentStatus {
    questUnitId: number;
    name: string;
    viaCurriculum: boolean;
    childQuests: MentorStudentQuestUnitChildQuestItem[];
}

export interface MentorStudentQuestUnitAssignmentData {
    userId: number;
    quests: MentorStudentQuestUnitAssignmentItem[];
}

export interface MentorStudentPickerItem {
    id: number;
    name: string;
    email: string;
}

export interface MentorStudentPickerListResponse {
    data: MentorStudentPickerItem[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

export interface MentorCurriculumDetail extends MentorCurriculumItem {
    unitIds: number[];
    units: Array<{
        id: number;
        title: string;
    }>;
    assignmentTarget: CurriculumAssignmentTarget;
    assignedStudentIds: number[];
}

export interface MentorCurriculumListResponse {
    data: MentorCurriculumItem[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

export interface MentorAssignmentUnitOption {
    id: number;
    title: string;
    isDirectlyAssigned: boolean;
    isEffective: boolean;
}

export interface MentorAssignmentCurriculumOption {
    id: number;
    name: string;
    description: string | null;
    unitCount: number;
    units: Array<{
        id: number;
        title: string;
    }>;
    isAssigned: boolean;
}

export interface MentorStudentAssignmentData {
    studentId: number;
    studentName: string;
    curricula: MentorAssignmentCurriculumOption[];
    units: MentorAssignmentUnitOption[];
}

export interface UpdateMentorStudentAssignmentsPayload {
    curriculumIds: number[];
    unitIds: number[];
}

export interface CreateMentorCurriculumPayload {
    name: string;
    description?: string | null;
    unitIds?: number[];
    assignmentTarget: CurriculumAssignmentTarget;
    studentIds?: number[];
}

export interface UpdateMentorCurriculumPayload extends CreateMentorCurriculumPayload {}
