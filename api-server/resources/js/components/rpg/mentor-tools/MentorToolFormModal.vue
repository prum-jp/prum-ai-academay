<template>
    <RpgModal
        :open="open"
        :title="modalTitle"
        :icon="modalIcon"
        @close="handleClose"
    >
        <form class="mentor-register-form" @submit.prevent="onSubmit">
            <div class="input-group">
                <label for="tool-form-name">{{ mentorToolFormModalConfig.nameLabel }}</label>
                <input
                    id="tool-form-name"
                    v-model="form.name"
                    type="text"
                    required
                    maxlength="255"
                    :placeholder="mentorToolFormPlaceholders.name"
                    :disabled="isSubmitting"
                    autocomplete="off"
                />
            </div>

            <p v-if="!isEditMode" class="mentor-register-note">{{ mentorToolFormNote }}</p>

            <p v-if="fieldErrors.name" class="login-error">{{ fieldErrors.name }}</p>
            <p v-else-if="errorMessage" class="login-error">{{ errorMessage }}</p>
        </form>

        <template #footer>
            <div class="mentor-quest-create-actions">
                <RpgButton type="button" :disabled="isSubmitting" @click="handleClose">
                    {{ mentorToolFormModalConfig.cancelLabel }}
                </RpgButton>
                <RpgButton
                    type="button"
                    :icon="submitIcon"
                    :disabled="isSubmitting"
                    @click="onSubmit"
                >
                    {{ submitLabel }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import type { MentorTool } from '@/types/mentor-quest/questAdmin';
import {
    mentorToolFormModalConfig,
    mentorToolFormNote,
    mentorToolFormPlaceholders,
} from '@/constants/mentor-tools/toolAdmin';
import { useMentorToolForm } from '@/composables/mentor-tools/useMentorToolForm';
import RpgButton from '@/components/rpg/shared/RpgButton.vue';
import RpgModal from '@/components/rpg/shared/RpgModal.vue';

const props = defineProps<{
    open: boolean;
    tool?: MentorTool | null;
}>();

const emit = defineEmits<{
    close: [];
    saved: [tool: MentorTool];
}>();

const isEditMode = computed(() => props.tool != null);
const modalTitle = computed(() =>
    isEditMode.value
        ? mentorToolFormModalConfig.editTitle
        : mentorToolFormModalConfig.createTitle,
);
const modalIcon = computed(() =>
    isEditMode.value ? mentorToolFormModalConfig.editIcon : mentorToolFormModalConfig.icon,
);
const submitIcon = computed(() =>
    isEditMode.value ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-plus',
);
const submitLabel = computed(() =>
    isSubmitting.value
        ? mentorToolFormModalConfig.submittingLabel
        : isEditMode.value
          ? mentorToolFormModalConfig.editSubmitLabel
          : mentorToolFormModalConfig.createSubmitLabel,
);

const { form, isSubmitting, errorMessage, fieldErrors, submit, resetForm } = useMentorToolForm(
    () => props.tool ?? null,
);

watch(
    () => [props.open, props.tool] as const,
    ([open]) => {
        if (open) {
            resetForm();
            return;
        }

        resetForm();
    },
);

const handleClose = (): void => {
    if (isSubmitting.value) {
        return;
    }

    emit('close');
};

const onSubmit = async (): Promise<void> => {
    const tool = await submit();
    if (!tool) {
        return;
    }

    emit('saved', tool);
};
</script>
