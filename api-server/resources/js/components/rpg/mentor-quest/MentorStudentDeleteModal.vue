<template>
    <RpgModal
        :open="open"
        :title="mentorStudentDeleteModalConfig.title"
        :icon="mentorStudentDeleteModalConfig.icon"
        @close="emit('close')"
    >
        <div class="mentor-quest-delete-modal">
            <p v-if="target" class="mentor-quest-delete-modal-target">
                {{ confirmMessage }}
            </p>

            <p class="mentor-quest-delete-modal-warning">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                {{ mentorStudentDeleteMessages.warning }}
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
                    {{ mentorStudentDeleteModalConfig.cancelLabel }}
                </RpgButton>
                <RpgButton
                    type="button"
                    tone="red"
                    icon="fa-solid fa-trash"
                    :disabled="isDeleting || !target"
                    @click="emit('confirm')"
                >
                    {{
                        isDeleting
                            ? mentorStudentDeleteModalConfig.deletingLabel
                            : mentorStudentDeleteModalConfig.confirmLabel
                    }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
    mentorStudentDeleteMessages,
    mentorStudentDeleteModalConfig,
} from '@/constants/mentor-quest/questAdmin';
import type { MentorStudent } from '@/types/mentor/mentor';
import RpgButton from '@/components/rpg/shared/RpgButton.vue';
import RpgModal from '@/components/rpg/shared/RpgModal.vue';

const props = defineProps<{
    open: boolean;
    target: MentorStudent | null;
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

    return mentorStudentDeleteMessages.confirm(props.target.name);
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
</style>
