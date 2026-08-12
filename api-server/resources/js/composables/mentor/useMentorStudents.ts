import { computed, ref } from 'vue';
import { fetchMentorStudents, selectMentorStudent } from '@/api/mentor/mentor';
import type { MentorStudent, MentorStudentListMeta } from '@/types/mentor/mentor';
import { mentorStudentMessages } from '@/constants/mentor/mentor';
import { usePaginatedSearchList } from '@/composables/shared/usePaginatedSearchList';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';
import { resolveSearchEmptyMessage } from '@/utils/student/studentListMessages';

export function useMentorStudents() {
    const list = usePaginatedSearchList<MentorStudent, MentorStudentListMeta>(
        fetchMentorStudents,
        mentorStudentMessages.loadFailed,
    );

    const isSelecting = ref(false);
    const selectError = ref('');

    const emptyMessage = computed((): string =>
        resolveSearchEmptyMessage(
            list.appliedQuery.value,
            mentorStudentMessages.emptySearch,
            mentorStudentMessages.emptyList,
        ),
    );

    const selectStudent = async (student: MentorStudent): Promise<MentorStudent | null> => {
        if (isSelecting.value) {
            return null;
        }

        isSelecting.value = true;
        selectError.value = '';

        try {
            const selected = await selectMentorStudent(student.id);
            list.items.value = list.items.value.map((item) => ({
                ...item,
                isSelected: item.id === selected.id,
            }));
            if (list.meta.value) {
                list.meta.value = {
                    ...list.meta.value,
                    selectedStudentId: selected.id,
                };
            }
            return selected;
        } catch (caughtError: unknown) {
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

    return {
        students: list.items,
        meta: list.meta,
        searchQuery: list.searchQuery,
        appliedQuery: list.appliedQuery,
        isLoading: list.isLoading,
        isSelecting,
        error: list.error,
        selectError,
        emptyMessage,
        loadStudents: list.loadPage,
        searchStudents: list.search,
        selectStudent,
    };
}
