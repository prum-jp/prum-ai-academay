import { computed, ref } from 'vue';
import { fetchMeRequest, loginRequest, logoutRequest } from '@/api/shared/auth';
import type { AuthUser } from '@/types/shared/auth';
import { ROLE_MENTOR, ROLE_STUDENT } from '@/types/shared/auth';

const user = ref<AuthUser | null>(null);
const initialized = ref(false);
let authRequestId = 0;

const hasMentorRole = (role: AuthUser['role'] | undefined): boolean => {
    return Number(role) === ROLE_MENTOR;
};

export function useAuth() {
    const isAuthenticated = computed(() => user.value !== null);
    const isMentor = computed(() => hasMentorRole(user.value?.role));
    const isStudent = computed(() => Number(user.value?.role) === ROLE_STUDENT);

    const fetchUser = async (): Promise<AuthUser | null> => {
        const currentRequestId = ++authRequestId;

        try {
            const fetchedUser = await fetchMeRequest();
            if (currentRequestId !== authRequestId) {
                return user.value;
            }

            user.value = fetchedUser;
            return fetchedUser;
        } catch {
            if (currentRequestId !== authRequestId) {
                return user.value;
            }

            user.value = null;
            return null;
        } finally {
            // Always unblock router bootstrap even if a stale request finishes last.
            initialized.value = true;
        }
    };

    const login = async (email: string, password: string): Promise<AuthUser> => {
        authRequestId += 1;

        const loggedInUser = await loginRequest(email, password);
        user.value = loggedInUser;
        initialized.value = true;

        return loggedInUser;
    };

    const logout = async (): Promise<void> => {
        authRequestId += 1;

        try {
            await logoutRequest();
        } catch {
            // Ignore network/abort errors; local session state is cleared below.
        } finally {
            user.value = null;
        }
    };

    const homePathFor = (authUser: AuthUser): string => {
        return hasMentorRole(authUser.role) ? '/mentor/quests' : '/';
    };

    return {
        user,
        initialized,
        isAuthenticated,
        isMentor,
        isStudent,
        fetchUser,
        login,
        logout,
        homePathFor,
    };
}
