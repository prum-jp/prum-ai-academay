import { onMounted, ref } from 'vue';
import { fetchMentorTools } from '@/api/toolAdmin';
import type { MentorTool } from '@/types/questAdmin';
import { mentorToolMessages } from '@/constants/toolAdmin';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

export function useMentorToolCatalog() {
    const tools = ref<MentorTool[]>([]);
    const isLoading = ref(true);
    const error = ref('');

    const loadTools = async (): Promise<void> => {
        isLoading.value = true;
        error.value = '';

        try {
            tools.value = await fetchMentorTools();
        } catch (caughtError: unknown) {
            error.value = extractApiErrorMessage(
                caughtError,
                undefined,
                mentorToolMessages.loadFailed,
            );
            tools.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    onMounted(() => {
        void loadTools();
    });

    return {
        tools,
        isLoading,
        error,
        loadTools,
    };
}
