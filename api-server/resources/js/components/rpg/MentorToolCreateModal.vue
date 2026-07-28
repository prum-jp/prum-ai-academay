<template>
    <RpgModal
        :open="open"
        :title="mentorToolCreateModalConfig.title"
        :icon="mentorToolCreateModalConfig.icon"
        @close="handleClose"
    >
        <form class="mentor-register-form" @submit.prevent="onSubmit">
            <div class="input-group">
                <label for="tool-create-code">{{ mentorToolCreateModalConfig.codeLabel }}</label>
                <input
                    id="tool-create-code"
                    v-model="form.code"
                    type="text"
                    required
                    maxlength="40"
                    :placeholder="mentorToolFormPlaceholders.code"
                    :disabled="isSubmitting"
                    autocomplete="off"
                />
                <p v-if="fieldErrors.code" class="login-error">{{ fieldErrors.code }}</p>
            </div>

            <div class="input-group">
                <label for="tool-create-name">{{ mentorToolCreateModalConfig.nameLabel }}</label>
                <input
                    id="tool-create-name"
                    v-model="form.name"
                    type="text"
                    required
                    maxlength="255"
                    :placeholder="mentorToolFormPlaceholders.name"
                    :disabled="isSubmitting"
                    autocomplete="off"
                />
                <p v-if="fieldErrors.name" class="login-error">{{ fieldErrors.name }}</p>
            </div>

            <p class="mentor-register-note">{{ mentorToolFormNote }}</p>

            <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>
        </form>

        <template #footer>
            <div class="mentor-quest-create-actions">
                <RpgButton type="button" :disabled="isSubmitting" @click="handleClose">
                    {{ mentorToolCreateModalConfig.cancelLabel }}
                </RpgButton>
                <RpgButton
                    type="button"
                    icon="fa-solid fa-plus"
                    :disabled="isSubmitting"
                    @click="onSubmit"
                >
                    {{
                        isSubmitting
                            ? mentorToolCreateModalConfig.submittingLabel
                            : mentorToolCreateModalConfig.submitLabel
                    }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { watch } from 'vue';
import type { MentorTool } from '@/types/questAdmin';
import {
    mentorToolCreateModalConfig,
    mentorToolFormNote,
    mentorToolFormPlaceholders,
} from '@/constants/toolAdmin';
import { useMentorToolCreate } from '@/composables/useMentorToolCreate';
import RpgButton from '@/components/rpg/RpgButton.vue';
import RpgModal from '@/components/rpg/RpgModal.vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
    created: [tool: MentorTool];
}>();

const { form, isSubmitting, errorMessage, fieldErrors, submit, resetForm } = useMentorToolCreate();

watch(
    () => props.open,
    (open) => {
        if (!open) {
            resetForm();
        }
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

    emit('created', tool);
};
</script>
