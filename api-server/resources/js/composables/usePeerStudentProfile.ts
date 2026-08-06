import { type MaybeRefOrGetter } from 'vue';
import { fetchPeerStudentProfile } from '@/api/students';
import { studentDirectoryMessages } from '@/constants/studentDirectory';
import { useAdventurerProfileLoader } from '@/composables/useRouteResourceLoader';

export function usePeerStudentProfile(studentId: MaybeRefOrGetter<number>) {
    return useAdventurerProfileLoader(
        studentId,
        fetchPeerStudentProfile,
        studentDirectoryMessages.profileLoadFailed,
    );
}
