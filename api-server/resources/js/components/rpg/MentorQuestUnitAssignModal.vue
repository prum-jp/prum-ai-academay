<template>
    <RpgModal
        :open="open"
        :title="title"
        :icon="mentorQuestUnitAssignModalConfig.icon"
        wide
        @close="onClose"
    >
        <p class="mentor-message">{{ description }}</p>

        <MentorStudentPicker
            :active="open"
            :assignment-target="assignmentTarget"
            :selected-student-ids="selectedStudentIds"
            :disabled="isSubmitting"
            :selection-error="selectionError"
            @update:assignment-target="assignmentTarget = $event"
            @update:selected-student-ids="selectedStudentIds = $event"
        />

        <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

        <template #footer>
            <div class="mentor-quest-unit-assign-modal-footer">
                <RpgButton
                    type="button"
                    icon="fa-solid fa-users"
                    :disabled="isSubmitting"
                    @click="onConfirm"
                >
                    {{
                        isSubmitting
                            ? submittingLabel
                            : submitLabel
                    }}
                </RpgButton>
                <RpgButton type="button" :disabled="isSubmitting" @click="onClose">
                    {{ mentorQuestUnitAssignModalConfig.cancelLabel }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { mentorCurriculumMessages } from '@/constants/curriculum';
import { mentorQuestUnitAssignModalConfig } from '@/constants/questAdmin';
import type { CurriculumAssignmentTarget } from '@/types/curriculum';
import MentorStudentPicker from '@/components/rpg/MentorStudentPicker.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import RpgModal from '@/components/rpg/RpgModal.vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        isSubmitting?: boolean;
        errorMessage?: string;
        title?: string;
        description?: string;
        submitLabel?: string;
        submittingLabel?: string;
    }>(),
    {
        isSubmitting: false,
        errorMessage: '',
        title: undefined,
        description: undefined,
        submitLabel: undefined,
        submittingLabel: undefined,
    },
);

const title = computed(
    (): string => props.title ?? mentorQuestUnitAssignModalConfig.title,
);
const description = computed(
    (): string => props.description ?? mentorQuestUnitAssignModalConfig.description,
);
const submitLabel = computed(
    (): string => props.submitLabel ?? mentorQuestUnitAssignModalConfig.submitLabel,
);
const submittingLabel = computed(
    (): string => props.submittingLabel ?? mentorQuestUnitAssignModalConfig.submittingLabel,
);

const emit = defineEmits<{
    close: [];
    confirm: [payload: { assignmentTarget: CurriculumAssignmentTarget; studentIds: number[] }];
}>();

const assignmentTarget = ref<CurriculumAssignmentTarget>('all');
const selectedStudentIds = ref<number[]>([]);
const selectionError = ref('');

const reset = (): void => {
    assignmentTarget.value = 'all';
    selectedStudentIds.value = [];
    selectionError.value = '';
};

const onClose = (): void => {
    emit('close');
};

const onConfirm = (): void => {
    selectionError.value = '';

    if (assignmentTarget.value === 'selected' && selectedStudentIds.value.length === 0) {
        selectionError.value = mentorCurriculumMessages.assignmentSelectedRequired;
        return;
    }

    emit('confirm', {
        assignmentTarget: assignmentTarget.value,
        studentIds: [...selectedStudentIds.value],
    });
};

watch(
    () => assignmentTarget.value,
    () => {
        selectionError.value = '';
    },
);

watch(
    () => selectedStudentIds.value,
    () => {
        selectionError.value = '';
    },
);

watch(
    () => props.open,
    (open) => {
        if (!open) {
            reset();
        }
    },
);

watch(
    () => assignmentTarget.value,
    (target) => {
        if (target === 'all') {
            selectedStudentIds.value = [];
        }
    },
);
</script>
