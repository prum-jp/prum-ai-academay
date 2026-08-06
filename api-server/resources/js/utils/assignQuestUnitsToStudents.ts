import { assignMentorQuestUnitToAllStudents } from '@/api/curriculum';
import { assignStudentQuestUnit } from '@/api/questUnitAssignment';
import type { CurriculumAssignmentTarget } from '@/types/curriculum';

export const assignQuestUnitsToStudents = async (
    unitIds: number[],
    assignmentTarget: CurriculumAssignmentTarget,
    studentIds: number[],
): Promise<void> => {
    for (const unitId of unitIds) {
        if (assignmentTarget === 'all') {
            await assignMentorQuestUnitToAllStudents(unitId);
            continue;
        }

        await Promise.all(
            studentIds.map((studentId) => assignStudentQuestUnit(studentId, unitId)),
        );
    }
};
