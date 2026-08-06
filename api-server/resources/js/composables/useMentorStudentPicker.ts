import { fetchMentorStudentPicker } from '@/api/curriculum';
import { mentorStudentMessages } from '@/constants/mentor';
import { usePaginatedSearchList } from '@/composables/usePaginatedSearchList';
import type { MentorStudentPickerItem } from '@/types/curriculum';
import type { PaginationMeta } from '@/types/pagination';

export function useMentorStudentPicker() {
    const list = usePaginatedSearchList<MentorStudentPickerItem, PaginationMeta>(
        fetchMentorStudentPicker,
        mentorStudentMessages.loadFailed,
        { loadOnMount: false },
    );

    return {
        students: list.items,
        meta: list.meta,
        searchQuery: list.searchQuery,
        appliedQuery: list.appliedQuery,
        isLoading: list.isLoading,
        error: list.error,
        loadStudents: list.loadPage,
        searchStudents: list.search,
        reset: list.reset,
    };
}
