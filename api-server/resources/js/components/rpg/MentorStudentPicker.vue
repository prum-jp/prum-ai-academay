<template>
    <section class="mentor-assignment-section">
        <h4>{{ mentorCurriculumMessages.assignmentSectionTitle }}</h4>

        <div class="mentor-assignment-target-options">
            <label class="mentor-assignment-target-option">
                <input
                    type="radio"
                    name="assignment-target"
                    value="all"
                    :checked="assignmentTarget === 'all'"
                    :disabled="disabled"
                    @change="$emit('update:assignmentTarget', 'all')"
                />
                <span>{{ mentorCurriculumMessages.assignmentTargetAll }}</span>
            </label>
            <label class="mentor-assignment-target-option">
                <input
                    type="radio"
                    name="assignment-target"
                    value="selected"
                    :checked="assignmentTarget === 'selected'"
                    :disabled="disabled"
                    @change="$emit('update:assignmentTarget', 'selected')"
                />
                <span>{{ mentorCurriculumMessages.assignmentTargetSelected }}</span>
            </label>
        </div>

        <div v-if="assignmentTarget === 'selected'" class="mentor-student-picker">
            <MentorStudentSearch
                v-model="searchQuery"
                :is-loading="isLoading"
                @search="searchStudents"
            />

            <AsyncState
                :is-loading="isLoading"
                :error="error ?? undefined"
                :is-empty="students.length === 0"
                :loading-message="mentorStudentMessages.loading"
                :empty-message="emptyMessage"
            >
                <ul class="mentor-assignment-list">
                    <li
                        v-for="student in students"
                        :key="student.id"
                        class="mentor-assignment-item"
                    >
                        <label class="mentor-assignment-label">
                            <input
                                type="checkbox"
                                :checked="selectedStudentIds.includes(student.id)"
                                :disabled="disabled"
                                @change="toggleStudent(student.id)"
                            />
                            <span class="mentor-assignment-item-body">
                                <strong>{{ student.name }}</strong>
                                <span class="mentor-assignment-meta">{{ student.email }}</span>
                            </span>
                        </label>
                    </li>
                </ul>
            </AsyncState>

            <QuestPagination
                v-if="meta"
                :meta="meta"
                :disabled="isLoading || disabled"
                @page-change="loadStudents"
            />

            <p v-if="selectionError" class="login-error">{{ selectionError }}</p>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import type { CurriculumAssignmentTarget } from '@/types/curriculum';
import { mentorCurriculumMessages } from '@/constants/curriculum';
import { mentorStudentMessages } from '@/constants/mentor';
import { useMentorStudentPicker } from '@/composables/useMentorStudentPicker';
import AsyncState from '@/components/rpg/AsyncState.vue';
import MentorStudentSearch from '@/components/rpg/MentorStudentSearch.vue';
import QuestPagination from '@/components/rpg/QuestPagination.vue';

const props = defineProps<{
    assignmentTarget: CurriculumAssignmentTarget;
    selectedStudentIds: number[];
    disabled?: boolean;
    selectionError?: string | null;
    active?: boolean;
}>();

const emit = defineEmits<{
    'update:assignmentTarget': [value: CurriculumAssignmentTarget];
    'update:selectedStudentIds': [value: number[]];
}>();

const {
    students,
    meta,
    searchQuery,
    appliedQuery,
    isLoading,
    error,
    loadStudents,
    searchStudents,
    reset,
} = useMentorStudentPicker();

const emptyMessage = computed((): string => {
    if (appliedQuery.value !== '') {
        return mentorCurriculumMessages.assignmentSelectedEmpty;
    }

    return mentorCurriculumMessages.assignmentSearchEmpty;
});

const toggleStudent = (studentId: number): void => {
    if (props.selectedStudentIds.includes(studentId)) {
        emit(
            'update:selectedStudentIds',
            props.selectedStudentIds.filter((id) => id !== studentId),
        );

        return;
    }

    emit('update:selectedStudentIds', [...props.selectedStudentIds, studentId]);
};

watch(
    () => [props.active, props.assignmentTarget] as const,
    async ([active, assignmentTarget]) => {
        if (!active || assignmentTarget !== 'selected') {
            return;
        }

        if (students.value.length === 0) {
            await loadStudents(1);
        }
    },
    { immediate: true },
);

watch(
    () => props.active,
    (active) => {
        if (!active) {
            reset();
        }
    },
);
</script>
