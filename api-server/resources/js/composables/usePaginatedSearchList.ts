import { onMounted, ref } from 'vue';
import type { PaginatedResponse, PaginationMeta } from '@/types/pagination';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

interface UsePaginatedSearchListOptions {
    loadOnMount?: boolean;
}

export function usePaginatedSearchList<T, M extends PaginationMeta = PaginationMeta>(
    fetchPage: (page: number, query: string) => Promise<PaginatedResponse<T, M>>,
    fallbackErrorMessage: string,
    options: UsePaginatedSearchListOptions = {},
) {
    const items = ref<T[]>([]);
    const meta = ref<M | null>(null);
    const searchQuery = ref('');
    const appliedQuery = ref('');
    const isLoading = ref(true);
    const error = ref('');

    let requestId = 0;

    const loadPage = async (page = 1): Promise<void> => {
        const currentRequestId = ++requestId;
        isLoading.value = true;
        error.value = '';

        try {
            const response = await fetchPage(page, appliedQuery.value);
            if (currentRequestId !== requestId) {
                return;
            }

            items.value = response.data;
            meta.value = response.meta;
        } catch (caughtError: unknown) {
            if (currentRequestId !== requestId) {
                return;
            }

            error.value = extractApiErrorMessage(
                caughtError,
                undefined,
                fallbackErrorMessage,
            );
            items.value = [];
            meta.value = null;
        } finally {
            if (currentRequestId === requestId) {
                isLoading.value = false;
            }
        }
    };

    const search = async (): Promise<void> => {
        appliedQuery.value = searchQuery.value.trim();
        await loadPage(1);
    };

    const reset = (): void => {
        items.value = [];
        meta.value = null;
        searchQuery.value = '';
        appliedQuery.value = '';
        error.value = '';
        isLoading.value = false;
    };

    if (options.loadOnMount !== false) {
        onMounted(() => {
            void loadPage();
        });
    }

    return {
        items,
        meta,
        searchQuery,
        appliedQuery,
        isLoading,
        error,
        loadPage,
        search,
        reset,
    };
}
