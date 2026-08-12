import { onMounted, ref, toValue, watch, type MaybeRefOrGetter } from 'vue';
import type { Ref } from 'vue';
import type { AdventurerProfile } from '@/types/profile/adventurer';
import { useAdventurerCardPreview } from '@/composables/profile/useAdventurerCardPreview';

interface UseRouteResourceLoaderOptions<T> {
    loadFailedMessage: string;
    fetch: (id: number) => Promise<T>;
    onSuccess?: (data: T) => void;
    loadOnMount?: boolean;
}

export function useRouteResourceLoader<T>(
    routeId: MaybeRefOrGetter<number>,
    options: UseRouteResourceLoaderOptions<T>,
) {
    const data = ref<T | null>(null) as Ref<T | null>;
    const isLoading = ref(true);
    const loadError = ref('');

    const load = async (): Promise<void> => {
        const id = toValue(routeId);
        if (!Number.isFinite(id) || id <= 0) {
            loadError.value = options.loadFailedMessage;
            isLoading.value = false;
            data.value = null;
            return;
        }

        isLoading.value = true;
        loadError.value = '';

        try {
            const result = await options.fetch(id);
            data.value = result;
            options.onSuccess?.(result);
        } catch {
            data.value = null;
            loadError.value = options.loadFailedMessage;
        } finally {
            isLoading.value = false;
        }
    };

    watch(
        () => toValue(routeId),
        () => {
            void load();
        },
    );

    if (options.loadOnMount !== false) {
        onMounted(() => {
            void load();
        });
    }

    return {
        data,
        isLoading,
        loadError,
        load,
    };
}

export function useAdventurerProfileLoader(
    routeId: MaybeRefOrGetter<number>,
    fetchProfile: (id: number) => Promise<AdventurerProfile & { navigation?: unknown }>,
    loadFailedMessage: string,
) {
    const profile = ref<AdventurerProfile | null>(null);
    const nextStudent = ref<{ id: number; name: string } | null>(null);

    const { data, isLoading, loadError, load } = useRouteResourceLoader(routeId, {
        loadFailedMessage,
        fetch: fetchProfile,
        onSuccess: (response) => {
            const { navigation, ...profileData } = response as AdventurerProfile & {
                navigation?: { next: { id: number; name: string } | null };
            };
            profile.value = profileData;
            nextStudent.value = navigation?.next ?? null;
        },
    });

    watch(data, (value) => {
        if (value === null) {
            profile.value = null;
            nextStudent.value = null;
        }
    });

    const cardPreview = useAdventurerCardPreview(profile);

    return {
        profile,
        nextStudent,
        isLoading,
        loadError,
        loadProfile: load,
        ...cardPreview,
    };
}
