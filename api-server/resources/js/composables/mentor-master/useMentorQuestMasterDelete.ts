import { ref } from 'vue';
import {
    deleteMentorQuest,
    deleteMentorQuestUnit,
    fetchMentorQuestDeletionImpact,
    fetchMentorQuestUnitDeletionImpact,
} from '@/api/mentor-quest/questAdmin';
import { mentorQuestMasterDeleteModalConfig } from '@/constants/mentor-master/questMaster';
import type {
    QuestDeletionImpact,
    QuestMasterDeleteTarget,
} from '@/types/mentor-master/questMaster';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';

export function useMentorQuestMasterDelete() {
    const deleteTarget = ref<QuestMasterDeleteTarget | null>(null);
    const isDeleteModalOpen = ref(false);
    const impact = ref<QuestDeletionImpact | null>(null);
    const isLoadingImpact = ref(false);
    const isDeleting = ref(false);
    const errorMessage = ref('');

    const resetState = (): void => {
        deleteTarget.value = null;
        impact.value = null;
        isLoadingImpact.value = false;
        isDeleting.value = false;
        errorMessage.value = '';
    };

    const openDeleteModal = async (target: QuestMasterDeleteTarget): Promise<void> => {
        deleteTarget.value = target;
        isDeleteModalOpen.value = true;
        impact.value = null;
        errorMessage.value = '';
        isLoadingImpact.value = true;

        try {
            impact.value =
                target.kind === 'personal_unit'
                    ? await fetchMentorQuestUnitDeletionImpact(target.id)
                    : await fetchMentorQuestDeletionImpact(target.id);
        } catch (error) {
            errorMessage.value =
                extractApiErrorMessage(error) ?? mentorQuestMasterDeleteModalConfig.loadFailed;
        } finally {
            isLoadingImpact.value = false;
        }
    };

    const closeDeleteModal = (): void => {
        if (isDeleting.value) {
            return;
        }

        isDeleteModalOpen.value = false;
        resetState();
    };

    const confirmDelete = async (): Promise<boolean> => {
        const target = deleteTarget.value;
        if (!target || isDeleting.value || isLoadingImpact.value || !impact.value || errorMessage.value) {
            return false;
        }

        isDeleting.value = true;
        errorMessage.value = '';

        try {
            if (target.kind === 'personal_unit') {
                await deleteMentorQuestUnit(target.id);
            } else {
                await deleteMentorQuest(target.id);
            }

            isDeleteModalOpen.value = false;
            resetState();
            return true;
        } catch (error) {
            errorMessage.value =
                extractApiErrorMessage(error) ?? mentorQuestMasterDeleteModalConfig.deleteFailed;
            return false;
        } finally {
            isDeleting.value = false;
        }
    };

    return {
        deleteTarget,
        isDeleteModalOpen,
        impact,
        isLoadingImpact,
        isDeleting,
        errorMessage,
        openDeleteModal,
        closeDeleteModal,
        confirmDelete,
    };
}
