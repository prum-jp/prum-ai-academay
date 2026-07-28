import { onMounted, ref } from 'vue';
import { fetchMentorStudents, selectMentorStudent } from '@/api/mentor';
import type { MentorStudent, MentorStudentListMeta } from '@/types/mentor';
import { mentorStudentMessages } from '@/constants/mentor';
import { useGameAudio } from '@/composables/useGameAudio';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

export function useMentorStudents() {
    const students = ref<MentorStudent[]>([]);
    const meta = ref<MentorStudentListMeta | null>(null);
    const searchQuery = ref('');
    const appliedQuery = ref('');
    const isLoading = ref(true);
    const isSelecting = ref(false);
    const error = ref('');
    const selectError = ref('');

    const { playSound } = useGameAudio();
    let requestId = 0;

    const loadStudents = async (page = 1): Promise<void> => {
        const currentRequestId = ++requestId;
        isLoading.value = true;
        error.value = '';

        try {
            const response = await fetchMentorStudents(page, appliedQuery.value);
            if (currentRequestId !== requestId) {
                return;
            }

            students.value = response.data;
            meta.value = response.meta;
        } catch (caughtError: unknown) {
            if (currentRequestId !== requestId) {
                return;
            }

            error.value = extractApiErrorMessage(
                caughtError,
                undefined,
                mentorStudentMessages.loadFailed,
            );
            students.value = [];
            meta.value = null;
        } finally {
            if (currentRequestId === requestId) {
                isLoading.value = false;
            }
        }
    };

    const searchStudents = async (): Promise<void> => {
        appliedQuery.value = searchQuery.value.trim();
        await loadStudents(1);
    };

    const selectStudent = async (student: MentorStudent): Promise<MentorStudent | null> => {
        if (isSelecting.value) {
            return null;
        }

        isSelecting.value = true;
        selectError.value = '';

        try {
            const selected = await selectMentorStudent(student.id);
            students.value = students.value.map((item) => ({
                ...item,
                isSelected: item.id === selected.id,
            }));
            if (meta.value) {
                meta.value = {
                    ...meta.value,
                    selectedStudentId: selected.id,
                };
            }
            playSound('click');
            return selected;
        } catch (caughtError: unknown) {
            playSound('down');
            selectError.value = extractApiErrorMessage(
                caughtError,
                'studentId',
                mentorStudentMessages.selectFailed,
            );
            return null;
        } finally {
            isSelecting.value = false;
        }
    };

    onMounted(() => {
        void loadStudents();
    });

    return {
        students,
        meta,
        searchQuery,
        appliedQuery,
        isLoading,
        isSelecting,
        error,
        selectError,
        loadStudents,
        searchStudents,
        selectStudent,
    };
}
