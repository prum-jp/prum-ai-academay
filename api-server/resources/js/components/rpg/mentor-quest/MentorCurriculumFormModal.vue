<template>
    <RpgModal
        :open="open"
        :title="modalTitle"
        icon="fa-solid fa-layer-group"
        wide
        @close="$emit('close')"
    >
        <div class="mentor-curriculum-form">
            <label :for="`${idPrefix}-name`">{{ mentorCurriculumMessages.nameLabel }}</label>
            <input
                :id="`${idPrefix}-name`"
                v-model="form.name"
                type="text"
                :placeholder="mentorCurriculumMessages.namePlaceholder"
                :disabled="isSaving"
            />

            <label :for="`${idPrefix}-description`">{{ mentorCurriculumMessages.descriptionLabel }}</label>
            <textarea
                :id="`${idPrefix}-description`"
                v-model="form.description"
                rows="3"
                :placeholder="mentorCurriculumMessages.descriptionPlaceholder"
                :disabled="isSaving"
            />

            <h4>{{ mentorCurriculumMessages.unitsLabel }}</h4>
            <p v-if="availableUnits.length === 0" class="mentor-assignment-empty">
                {{ mentorAssignmentMessages.emptyUnits }}
            </p>
            <ul v-else class="mentor-assignment-list">
                <li v-for="unit in availableUnits" :key="unit.id" class="mentor-assignment-item">
                    <label class="mentor-assignment-label">
                        <input
                            v-model="form.unitIds"
                            type="checkbox"
                            :value="unit.id"
                            :disabled="isSaving"
                        />
                        <span class="mentor-assignment-item-body">
                            <strong>{{ unit.title }}</strong>
                        </span>
                    </label>
                </li>
            </ul>

            <MentorStudentPicker
                :active="open"
                :assignment-target="form.assignmentTarget"
                :selected-student-ids="form.studentIds"
                :disabled="isSaving"
                :selection-error="selectionError"
                @update:assignment-target="form.assignmentTarget = $event"
                @update:selected-student-ids="form.studentIds = $event"
            />

            <p v-if="error" class="login-error">{{ error }}</p>
        </div>

        <template #footer>
            <button
                type="button"
                class="rpg-btn is-secondary"
                :disabled="isSaving"
                @click="$emit('close')"
            >
                キャンセル
            </button>
            <button
                type="button"
                class="rpg-btn is-primary"
                :disabled="isSaving || form.name.trim() === ''"
                @click="onSubmit"
            >
                {{
                    isSaving ? mentorCurriculumMessages.savingLabel : mentorCurriculumMessages.saveLabel
                }}
            </button>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { reactive, ref, useId, watch } from 'vue';
import {
    createMentorCurriculum,
    fetchMentorCurriculumDetail,
    updateMentorCurriculum,
} from '@/api/mentor-quest/curriculum';
import { fetchMentorQuestUnits } from '@/api/mentor-quest/questAdmin';
import {
    mentorAssignmentMessages,
    mentorCurriculumMessages,
} from '@/constants/mentor-quest/curriculum';
import type { CurriculumAssignmentTarget } from '@/types/mentor-quest/curriculum';
import type { MentorQuestUnitItem } from '@/types/mentor-quest/questAdmin';
import MentorStudentPicker from '@/components/rpg/mentor/MentorStudentPicker.vue';
import RpgModal from '@/components/rpg/shared/RpgModal.vue';

const props = defineProps<{
    open: boolean;
    curriculumId: number | null;
}>();

const emit = defineEmits<{
    close: [];
    saved: [message: string];
}>();

const idPrefix = useId();
const isSaving = ref(false);
const error = ref<string | null>(null);
const selectionError = ref<string | null>(null);
const availableUnits = ref<MentorQuestUnitItem[]>([]);

const form = reactive({
    name: '',
    description: '',
    unitIds: [] as number[],
    assignmentTarget: 'all' as CurriculumAssignmentTarget,
    studentIds: [] as number[],
});

const modalTitle = ref<string>(mentorCurriculumMessages.createTitle);

const resetForm = (): void => {
    form.name = '';
    form.description = '';
    form.unitIds = [];
    form.assignmentTarget = 'all';
    form.studentIds = [];
    error.value = null;
    selectionError.value = null;
    modalTitle.value = mentorCurriculumMessages.createTitle;
};

const loadAvailableUnits = async (): Promise<void> => {
    const response = await fetchMentorQuestUnits(1);
    availableUnits.value = response.data;
};

const loadCurriculum = async (curriculumId: number): Promise<void> => {
    const detail = await fetchMentorCurriculumDetail(curriculumId);
    form.name = detail.name;
    form.description = detail.description ?? '';
    form.unitIds = [...detail.unitIds];
    form.assignmentTarget = detail.assignmentTarget;
    form.studentIds = [...detail.assignedStudentIds];
    modalTitle.value = mentorCurriculumMessages.editTitle;
};

watch(
    () => [props.open, props.curriculumId] as const,
    async ([isOpen, curriculumId]) => {
        if (!isOpen) {
            resetForm();

            return;
        }

        try {
            await loadAvailableUnits();

            if (curriculumId !== null) {
                await loadCurriculum(curriculumId);
            } else {
                resetForm();
            }
        } catch {
            error.value = mentorCurriculumMessages.loadFailed;
        }
    },
);

const validateSelection = (): boolean => {
    if (form.assignmentTarget === 'selected' && form.studentIds.length === 0) {
        selectionError.value = mentorCurriculumMessages.assignmentSelectedRequired;

        return false;
    }

    selectionError.value = null;

    return true;
};

const onSubmit = async (): Promise<void> => {
    if (!validateSelection()) {
        return;
    }

    isSaving.value = true;
    error.value = null;

    const payload = {
        name: form.name.trim(),
        description: form.description.trim() === '' ? null : form.description.trim(),
        unitIds: form.unitIds,
        assignmentTarget: form.assignmentTarget,
        studentIds: form.assignmentTarget === 'selected' ? form.studentIds : [],
    };

    try {
        if (props.curriculumId !== null) {
            await updateMentorCurriculum(props.curriculumId, payload);
            emit('saved', mentorCurriculumMessages.updateSuccess);
        } else {
            await createMentorCurriculum(payload);
            emit('saved', mentorCurriculumMessages.createSuccess);
        }

        emit('close');
    } catch {
        error.value = mentorCurriculumMessages.saveFailed;
    } finally {
        isSaving.value = false;
    }
};
</script>
