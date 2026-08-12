import { computed } from 'vue';
import { fetchStudentDirectory } from '@/api/student/students';
import { studentDirectoryMessages } from '@/constants/student/studentDirectory';
import { usePaginatedSearchList } from '@/composables/shared/usePaginatedSearchList';
import type { StudentListItem } from '@/types/student/studentList';
import type { PaginationMeta } from '@/types/shared/pagination';
import { resolveSearchEmptyMessage } from '@/utils/student/studentListMessages';

export function useStudentDirectory() {
    const list = usePaginatedSearchList<StudentListItem, PaginationMeta>(
        fetchStudentDirectory,
        studentDirectoryMessages.loadFailed,
    );

    const emptyMessage = computed((): string =>
        resolveSearchEmptyMessage(
            list.appliedQuery.value,
            studentDirectoryMessages.emptySearch,
            studentDirectoryMessages.emptyList,
        ),
    );

    return {
        students: list.items,
        meta: list.meta,
        searchQuery: list.searchQuery,
        appliedQuery: list.appliedQuery,
        isLoading: list.isLoading,
        error: list.error,
        emptyMessage,
        loadStudents: list.loadPage,
        searchStudents: list.search,
    };
}
