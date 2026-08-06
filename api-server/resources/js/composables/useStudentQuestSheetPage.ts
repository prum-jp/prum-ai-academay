import { computed, onMounted, ref, watch, type ComputedRef } from 'vue';
import { useRoute } from 'vue-router';
import { fetchQuest } from '@/api/quests';
import { useAuth } from '@/composables/useAuth';
import { useMentorReviewRequests } from '@/composables/useMentorReviewRequests';
import { useQuestComments } from '@/composables/useQuestComments';
import { useQuestProgress } from '@/composables/useQuestProgress';
import { questSheetConfig } from '@/constants/questSheet';
import type { QuestProgressStatus } from '@/constants/questProgress';
import type { QuestItem } from '@/types/quest';
import { parseQuestDescriptionSections } from '@/utils/questDescriptionSections';
import { buildQuestMetaRows } from '@/utils/questSheetMeta';
import type { QuestSheetSectionItem } from '@/components/rpg/QuestSheetContentSections.vue';

export function useStudentQuestSheetPage() {
    const route = useRoute();
    const { isMentor } = useAuth();
    const { isUpdating, updateQuestStatus } = useQuestProgress();
    const { refresh: refreshReviewRequests } = useMentorReviewRequests();

    const quest = ref<QuestItem | null>(null);
    const isLoading = ref(true);
    const loadError = ref('');

    const questId = computed(() => Number(route.params.questId));
    const {
        items: commentItems,
        isLoading: isCommentsLoading,
        loadError: commentsLoadError,
        load: loadComments,
        post: postComment,
        refresh: refreshComments,
    } = useQuestComments(questId);

    const questNo = computed(() => quest.value?.sortOrder ?? null);
    const metaRows = computed(() => (quest.value ? buildQuestMetaRows(quest.value) : []));

    const sections: ComputedRef<QuestSheetSectionItem[]> = computed(() => {
        if (!quest.value) {
            return [];
        }

        const parsed = parseQuestDescriptionSections(
            quest.value.description,
            quest.value.clearCondition,
        );

        return [
            { title: questSheetConfig.sections.overview, body: parsed.overview },
            { title: questSheetConfig.sections.purpose, body: parsed.purpose },
            {
                title: questSheetConfig.sections.deliverable,
                body: parsed.deliverable,
                kind: 'deliverable',
            },
            {
                title: questSheetConfig.sections.completionCondition,
                body: parsed.completionCondition,
            },
        ];
    });

    const loadQuest = async (): Promise<void> => {
        if (!Number.isFinite(questId.value) || questId.value <= 0) {
            loadError.value = questSheetConfig.loadQuestFailed;
            isLoading.value = false;
            return;
        }

        isLoading.value = true;
        loadError.value = '';

        try {
            quest.value = await fetchQuest(questId.value);
        } catch {
            quest.value = null;
            loadError.value = questSheetConfig.loadQuestFailed;
        } finally {
            isLoading.value = false;
        }
    };

    const updateStatus = async (status: QuestProgressStatus): Promise<void> => {
        if (!quest.value) {
            return;
        }

        const role = isMentor.value ? 'mentor' : 'student';
        const updated = await updateQuestStatus(quest.value, status, role);

        if (!updated) {
            return;
        }

        quest.value = updated;
        await refreshComments();

        if (isMentor.value) {
            void refreshReviewRequests();
        }
    };

    const onSubmissionSaved = async (updated: QuestItem): Promise<void> => {
        quest.value = updated;
        await refreshComments();
    };

    onMounted(() => {
        void loadQuest();
        void loadComments();
    });

    watch(questId, () => {
        void loadQuest();
    });

    return {
        quest,
        isLoading,
        loadError,
        isMentor,
        isUpdating,
        questNo,
        metaRows,
        sections,
        commentItems,
        isCommentsLoading,
        commentsLoadError,
        postComment,
        loadQuest,
        updateStatus,
        onSubmissionSaved,
    };
}
