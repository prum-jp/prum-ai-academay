import { computed } from 'vue';
import { fetchStudentDirectory } from '@/api/students';
import { studentDirectoryMessages } from '@/constants/studentDirectory';
import { usePaginatedSearchList } from '@/composables/usePaginatedSearchList';
import type { StudentListItem } from '@/types/studentList';
import type { PaginationMeta } from '@/types/pagination';
import { resolveSearchEmptyMessage } from '@/utils/studentListMessages';

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
