<template>
    <RpgModal
        :open="open"
        :title="modalTitle"
        :icon="mentorQuestEditModalConfig.icon"
        wide
        @close="handleClose"
    >
        <div v-if="isLoading" class="mentor-quest-edit-loading">
            {{ mentorQuestAdminMessages.loading }}
        </div>

        <form v-else class="mentor-quest-create-form" @submit.prevent="onSubmit">
            <template v-if="kind === 'unit'">
                <MentorUnitFormFields
                    :form="unitForm"
                    :field-errors="fieldErrors"
                    id-prefix="edit-unit"
                    :disabled="isSubmitting"
                />

                <MentorChildQuestFields
                    :quests="unitForm.quests"
                    :tools="tools"
                    :disabled="isSubmitting"
                    @add="addChildQuest"
                    @remove="removeChildQuest"
                />
            </template>

            <MentorQuestFormFields
                v-else
                :form="questForm"
                :field-errors="fieldErrors"
                id-prefix="edit-quest"
                :disabled="isSubmitting"
            />

            <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>
        </form>

        <template #footer>
            <div class="mentor-quest-create-actions">
                <RpgButton type="button" :disabled="isSubmitting" @click="handleClose">
                    {{ mentorQuestEditModalConfig.cancelLabel }}
                </RpgButton>
                <RpgButton
                    type="button"
                    icon="fa-solid fa-floppy-disk"
                    :disabled="isSubmitting || isLoading"
                    @click="onSubmit"
                >
                    {{
                        isSubmitting
                            ? mentorQuestEditModalConfig.submittingLabel
                            : mentorQuestEditModalConfig.submitLabel
                    }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import {
    mentorQuestAdminMessages,
    mentorQuestEditModalConfig,
} from '@/constants/questAdmin';
import type { MentorQuestItem, MentorQuestUnitItem } from '@/types/questAdmin';
import { useMentorQuestEdit } from '@/composables/useMentorQuestEdit';
import MentorChildQuestFields from '@/components/rpg/MentorChildQuestFields.vue';
import MentorQuestFormFields from '@/components/rpg/MentorQuestFormFields.vue';
import MentorUnitFormFields from '@/components/rpg/MentorUnitFormFields.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import RpgModal from '@/components/rpg/RpgModal.vue';

const props = defineProps<{
    open: boolean;
    kind: 'unit' | 'quest';
    unit: MentorQuestUnitItem | null;
    quest: MentorQuestItem | null;
}>();

const emit = defineEmits<{
    close: [];
    updated: [kind: 'unit' | 'quest'];
}>();

const {
    tools,
    unitForm,
    questForm,
    isLoading,
    isSubmitting,
    errorMessage,
    fieldErrors,
    initUnit,
    initQuest,
    addChildQuest,
    removeChildQuest,
    submitUnit,
    submitQuest,
} = useMentorQuestEdit();

const modalTitle = computed(() =>
    props.kind === 'unit'
        ? mentorQuestEditModalConfig.unitTitle
        : mentorQuestEditModalConfig.questTitle,
);

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        if (props.kind === 'unit' && props.unit) {
            void initUnit(props.unit);
        } else if (props.kind === 'quest' && props.quest) {
            initQuest(props.quest);
        }
    },
);

const handleClose = (): void => {
    emit('close');
};

const onSubmit = async (): Promise<void> => {
    const success = props.kind === 'unit' ? await submitUnit() : await submitQuest();
    if (!success) {
        return;
    }

    emit('updated', props.kind);
};
</script>
