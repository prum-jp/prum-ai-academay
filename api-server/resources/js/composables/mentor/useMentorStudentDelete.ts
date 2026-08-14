import { ref } from 'vue';
import { deleteMentorStudent } from '@/api/mentor/mentor';
import { mentorStudentDeleteModalConfig } from '@/constants/mentor-quest/questAdmin';
import type { MentorStudent } from '@/types/mentor/mentor';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';

export function useMentorStudentDelete(onDeleted: () => Promise<void> | void) {
    const deleteTarget = ref<MentorStudent | null>(null);
    const isDeleteModalOpen = ref(false);
    const isDeleting = ref(false);
    const errorMessage = ref('');

    const resetState = (): void => {
        deleteTarget.value = null;
        isDeleting.value = false;
        errorMessage.value = '';
    };

    const openDeleteModal = (student: MentorStudent): void => {
        deleteTarget.value = student;
        isDeleteModalOpen.value = true;
        errorMessage.value = '';
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
        if (!target || isDeleting.value) {
            return false;
        }

        isDeleting.value = true;
        errorMessage.value = '';

        try {
            await deleteMentorStudent(target.id);
            isDeleteModalOpen.value = false;
            resetState();
            await onDeleted();

            return true;
        } catch (error) {
            errorMessage.value =
                extractApiErrorMessage(error) ?? mentorStudentDeleteModalConfig.deleteFailed;

            return false;
        } finally {
            isDeleting.value = false;
        }
    };

    return {
        deleteTarget,
        isDeleteModalOpen,
        isDeleting,
        errorMessage,
        openDeleteModal,
        closeDeleteModal,
        confirmDelete,
    };
}
