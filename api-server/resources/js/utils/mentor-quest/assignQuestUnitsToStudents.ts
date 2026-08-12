import { assignMentorQuestUnitToAllStudents } from '@/api/mentor-quest/curriculum';
import { assignStudentQuestUnit } from '@/api/mentor-quest/questUnitAssignment';
import type { CurriculumAssignmentTarget } from '@/types/mentor-quest/curriculum';

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
