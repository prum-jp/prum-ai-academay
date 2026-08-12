import { type MaybeRefOrGetter } from 'vue';
import { fetchPeerStudentProfile } from '@/api/student/students';
import { studentDirectoryMessages } from '@/constants/student/studentDirectory';
import { useAdventurerProfileLoader } from '@/composables/shared/useRouteResourceLoader';

export function usePeerStudentProfile(studentId: MaybeRefOrGetter<number>) {
    return useAdventurerProfileLoader(
        studentId,
        fetchPeerStudentProfile,
        studentDirectoryMessages.profileLoadFailed,
    );
}
