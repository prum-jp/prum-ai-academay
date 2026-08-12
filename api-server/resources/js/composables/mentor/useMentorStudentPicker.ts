import { fetchMentorStudentPicker } from '@/api/mentor-quest/curriculum';
import { mentorStudentMessages } from '@/constants/mentor/mentor';
import { usePaginatedSearchList } from '@/composables/shared/usePaginatedSearchList';
import type { MentorStudentPickerItem } from '@/types/mentor-quest/curriculum';
import type { PaginationMeta } from '@/types/shared/pagination';

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
