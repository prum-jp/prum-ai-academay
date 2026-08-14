<template>
    <RpgModal
        :open="open"
        :title="mentorQuestMasterDeleteModalConfig.title"
        :icon="mentorQuestMasterDeleteModalConfig.icon"
        @close="emit('close')"
    >
        <div class="mentor-quest-delete-modal">
            <p v-if="target" class="mentor-quest-delete-modal-target">
                {{ confirmMessage }}
            </p>

            <p v-if="isLoadingImpact" class="mentor-quest-delete-modal-note">
                {{ mentorQuestMasterDeleteModalConfig.loadingLabel }}
            </p>

            <template v-else-if="impact && impact.linkedUserCount > 0">
                <p class="mentor-quest-delete-modal-warning">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    {{ mentorQuestMasterDeleteMessages.linkedUsersWarning(impact.linkedUserCount) }}
                </p>
            </template>

            <p
                v-if="impact?.hasSubmissions"
                class="mentor-quest-delete-modal-warning"
            >
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                {{ mentorQuestMasterDeleteMessages.submissionsWarning }}
            </p>

            <p
                v-if="target?.kind === 'personal_unit' && (impact?.childQuestCount ?? 0) > 0"
                class="mentor-quest-delete-modal-note"
            >
                {{ mentorQuestMasterDeleteMessages.unitChildQuestCount(impact?.childQuestCount ?? 0) }}
            </p>

            <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>
        </div>

        <template #footer>
            <div class="mentor-quest-create-actions">
                <RpgButton
                    type="button"
                    :disabled="isDeleting"
                    @click="emit('close')"
                >
                    {{ mentorQuestMasterDeleteModalConfig.cancelLabel }}
                </RpgButton>
                <RpgButton
                    type="button"
                    tone="red"
                    icon="fa-solid fa-trash"
                    :disabled="isDeleting || isLoadingImpact || !target || !impact || !!errorMessage"
                    @click="emit('confirm')"
                >
                    {{
                        isDeleting
                            ? mentorQuestMasterDeleteModalConfig.deletingLabel
                            : mentorQuestMasterDeleteModalConfig.confirmLabel
                    }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
    mentorQuestMasterDeleteMessages,
    mentorQuestMasterDeleteModalConfig,
} from '@/constants/mentor-master/questMaster';
import type {
    QuestDeletionImpact,
    QuestMasterDeleteTarget,
} from '@/types/mentor-master/questMaster';
import RpgButton from '@/components/rpg/shared/RpgButton.vue';
import RpgModal from '@/components/rpg/shared/RpgModal.vue';

const props = defineProps<{
    open: boolean;
    target: QuestMasterDeleteTarget | null;
    impact: QuestDeletionImpact | null;
    isLoadingImpact: boolean;
    isDeleting: boolean;
    errorMessage: string;
}>();

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

const confirmMessage = computed((): string => {
    if (!props.target) {
        return '';
    }

    return props.target.kind === 'personal_unit'
        ? mentorQuestMasterDeleteMessages.unitConfirm(props.target.title)
        : mentorQuestMasterDeleteMessages.questConfirm(props.target.title);
});
</script>

<style scoped>
.mentor-quest-delete-modal {
    display: grid;
    gap: 12px;
}

.mentor-quest-delete-modal-target {
    margin: 0;
    font-weight: 800;
    line-height: 1.6;
}

.mentor-quest-delete-modal-warning {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    margin: 0;
    padding: 12px;
    border: 2px solid #d64545;
    border-radius: 10px;
    background: #fff5f5;
    color: #a02828;
    font-size: 14px;
    font-weight: 800;
    line-height: 1.6;
}

.mentor-quest-delete-modal-note {
    margin: 0;
    color: #555;
    font-size: 14px;
    line-height: 1.6;
}
</style>
