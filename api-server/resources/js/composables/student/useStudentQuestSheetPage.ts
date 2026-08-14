import { computed, onMounted, ref, watch, type ComputedRef } from 'vue';
import { useRoute } from 'vue-router';
import { fetchQuest } from '@/api/quest/quests';
import { useAuth } from '@/composables/shared/useAuth';
import { useMentorReviewRequests } from '@/composables/mentor/useMentorReviewRequests';
import { useQuestComments } from '@/composables/quest/useQuestComments';
import { useQuestProgress } from '@/composables/quest/useQuestProgress';
import { useToastNotice } from '@/composables/shared/useToastNotice';
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import {
    questProgressReviewRequiresSubmissionMessage,
    type QuestProgressStatus,
} from '@/constants/quest/questProgress';
import type { QuestItem } from '@/types/quest/quest';
import { parseQuestDescriptionSections } from '@/utils/quest-sheet/questDescriptionSections';
import { buildQuestMetaRows } from '@/utils/quest-sheet/questSheetMeta';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';
import type { QuestSheetSectionItem } from '@/components/rpg/quest-sheet/QuestSheetContentSections.vue';

export function useStudentQuestSheetPage() {
    const route = useRoute();
    const { isMentor } = useAuth();
    const { isUpdating, updateQuestStatus } = useQuestProgress();
    const { refresh: refreshReviewRequests } = useMentorReviewRequests();
    const { showToast, toastMessage, showNotice } = useToastNotice();

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

        if (status === 'review_requested' && !quest.value.submission && !isMentor.value) {
            showNotice(questProgressReviewRequiresSubmissionMessage);
            return;
        }

        const role = isMentor.value ? 'mentor' : 'student';

        try {
            const updated = await updateQuestStatus(quest.value, status, role);

            if (!updated) {
                return;
            }

            quest.value = updated;
            await refreshComments();

            if (isMentor.value) {
                void refreshReviewRequests();
            }
        } catch (error) {
            showNotice(extractApiErrorMessage(error, 'status'));
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
        showToast,
        toastMessage,
    };
}
