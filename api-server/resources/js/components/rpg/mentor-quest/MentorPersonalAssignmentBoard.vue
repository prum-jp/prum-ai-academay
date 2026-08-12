<template>
    <section class="quest-section mentor-personal-assignment-board">
        <header class="quest-section-header">
            <i :class="mentorPersonalAssignmentSectionConfig.icon"></i>
            <h3>{{ mentorPersonalAssignmentSectionConfig.title }}</h3>
        </header>

        <p class="mentor-message">{{ mentorPersonalAssignmentSectionConfig.description }}</p>

        <StudentListSection
            :items="students"
            :meta="meta"
            :search-query="searchQuery"
            :is-loading="isLoading"
            :error="error"
            :empty-message="emptyMessage"
            :loading-message="mentorStudentMessages.loading"
            :search-label="mentorStudentSearchConfig.label"
            :search-placeholder="mentorStudentSearchConfig.placeholder"
            :pagination-disabled="disabled || isSelecting"
            @update:search-query="searchQuery = $event"
            @search="searchStudents"
            @page-change="loadStudents"
        >
            <template #item="{ item: student }">
                <MentorPersonalAssignmentRow
                    :student="student"
                    :disabled="disabled || isSelecting"
                    @open-assign="openAssignModal"
                    @open-home="onOpenHome"
                />
            </template>

            <template #footer>
                <p v-if="selectError" class="login-error">{{ selectError }}</p>
            </template>
        </StudentListSection>

        <MentorStudentQuestUnitAssignModal
            :open="assignModalStudent !== null"
            :student-id="assignModalStudent?.id ?? null"
            :student-name="assignModalStudent?.name ?? ''"
            :disabled="disabled"
            @close="closeAssignModal"
            @notify="(message) => emit('notify', message)"
        />
    </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { mentorPersonalAssignmentSectionConfig } from '@/constants/mentor-quest/questAdmin';
import { mentorStudentMessages, mentorStudentSearchConfig } from '@/constants/mentor/mentor';
import type { MentorStudent } from '@/types/mentor/mentor';
import { useMentorStudents } from '@/composables/mentor/useMentorStudents';
import MentorPersonalAssignmentRow from '@/components/rpg/mentor-quest/MentorPersonalAssignmentRow.vue';
import MentorStudentQuestUnitAssignModal from '@/components/rpg/mentor/MentorStudentQuestUnitAssignModal.vue';
import StudentListSection from '@/components/rpg/student/StudentListSection.vue';

defineProps<{
    disabled?: boolean;
}>();

const emit = defineEmits<{
    notify: [message: string];
}>();

const router = useRouter();
const assignModalStudent = ref<MentorStudent | null>(null);

const {
    students,
    meta,
    searchQuery,
    isLoading,
    isSelecting,
    error,
    selectError,
    emptyMessage,
    loadStudents,
    searchStudents,
    selectStudent,
} = useMentorStudents();

const openAssignModal = (student: MentorStudent): void => {
    assignModalStudent.value = student;
};

const closeAssignModal = (): void => {
    assignModalStudent.value = null;
};

const onOpenHome = async (student: MentorStudent): Promise<void> => {
    const selected = await selectStudent(student);
    if (!selected) {
        return;
    }

    await router.push({ name: 'student-sheet' });
};
</script>
