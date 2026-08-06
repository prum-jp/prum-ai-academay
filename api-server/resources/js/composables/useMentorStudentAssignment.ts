import { ref } from 'vue';
import {
    fetchMentorStudentAssignments,
    updateMentorStudentAssignments,
} from '@/api/curriculum';
import { mentorAssignmentMessages } from '@/constants/curriculum';
import type { MentorStudentAssignmentData } from '@/types/curriculum';

export function useMentorStudentAssignment() {
    const assignmentData = ref<MentorStudentAssignmentData | null>(null);
    const selectedCurriculumIds = ref<number[]>([]);
    const selectedUnitIds = ref<number[]>([]);
    const isLoading = ref(false);
    const isSaving = ref(false);
    const error = ref<string | null>(null);

    const syncSelectionsFromData = (data: MentorStudentAssignmentData): void => {
        selectedCurriculumIds.value = data.curricula
            .filter((curriculum) => curriculum.isAssigned)
            .map((curriculum) => curriculum.id);
        selectedUnitIds.value = data.units
            .filter((unit) => unit.isDirectlyAssigned)
            .map((unit) => unit.id);
    };

    const loadAssignments = async (studentId: number): Promise<boolean> => {
        isLoading.value = true;
        error.value = null;

        try {
            const data = await fetchMentorStudentAssignments(studentId);
            assignmentData.value = data;
            syncSelectionsFromData(data);

            return true;
        } catch {
            error.value = mentorAssignmentMessages.loadFailed;
            assignmentData.value = null;

            return false;
        } finally {
            isLoading.value = false;
        }
    };

    const saveAssignments = async (studentId: number): Promise<boolean> => {
        isSaving.value = true;
        error.value = null;

        try {
            const data = await updateMentorStudentAssignments(studentId, {
                curriculumIds: selectedCurriculumIds.value,
                unitIds: selectedUnitIds.value,
            });
            assignmentData.value = data;
            syncSelectionsFromData(data);

            return true;
        } catch {
            error.value = mentorAssignmentMessages.saveFailed;

            return false;
        } finally {
            isSaving.value = false;
        }
    };

    const toggleCurriculum = (curriculumId: number): void => {
        if (selectedCurriculumIds.value.includes(curriculumId)) {
            selectedCurriculumIds.value = selectedCurriculumIds.value.filter(
                (id) => id !== curriculumId,
            );

            return;
        }

        selectedCurriculumIds.value = [...selectedCurriculumIds.value, curriculumId];
    };

    const toggleUnit = (unitId: number): void => {
        if (selectedUnitIds.value.includes(unitId)) {
            selectedUnitIds.value = selectedUnitIds.value.filter((id) => id !== unitId);

            return;
        }

        selectedUnitIds.value = [...selectedUnitIds.value, unitId];
    };

    const reset = (): void => {
        assignmentData.value = null;
        selectedCurriculumIds.value = [];
        selectedUnitIds.value = [];
        error.value = null;
    };

    return {
        assignmentData,
        selectedCurriculumIds,
        selectedUnitIds,
        isLoading,
        isSaving,
        error,
        loadAssignments,
        saveAssignments,
        toggleCurriculum,
        toggleUnit,
        reset,
    };
}
