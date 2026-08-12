import { computed, ref, watch, type Ref } from 'vue';
import { fetchQuestComments, postQuestComment } from '@/api/quest/questComments';
import { questCommentsConfig } from '@/constants/quest/questComments';
import type { QuestComment } from '@/types/quest/questComment';

export function useQuestComments(questId: Ref<number>) {
    const items = ref<QuestComment[]>([]);
    const isLoading = ref(true);
    const loadError = ref('');

    const load = async (): Promise<void> => {
        isLoading.value = true;
        loadError.value = '';

        try {
            items.value = await fetchQuestComments(questId.value);
        } catch {
            items.value = [];
            loadError.value = questCommentsConfig.loadFailed;
        } finally {
            isLoading.value = false;
        }
    };

    const append = (comment: QuestComment): void => {
        items.value = [...items.value, comment];
    };

    const post = async (body: string): Promise<QuestComment | null> => {
        try {
            const created = await postQuestComment(questId.value, body);
            append(created);
            return created;
        } catch {
            return null;
        }
    };

    const refresh = async (): Promise<void> => {
        await load();
    };

    watch(questId, () => {
        void load();
    });

    const isEmpty = computed(() => !isLoading.value && items.value.length === 0);

    return {
        items,
        isLoading,
        loadError,
        isEmpty,
        load,
        append,
        post,
        refresh,
    };
}
