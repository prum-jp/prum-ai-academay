<template>
    <RpgCard :title="studentDirectoryPageConfig.title" :icon="studentDirectoryPageConfig.icon">
        <p class="mentor-message">{{ studentDirectoryPageConfig.description }}</p>

        <StudentListSection
            :items="students"
            :meta="meta"
            :search-query="searchQuery"
            :is-loading="isLoading"
            :error="error"
            :empty-message="emptyMessage"
            :loading-message="studentDirectoryMessages.loading"
            :search-label="studentDirectorySearchConfig.label"
            :search-placeholder="studentDirectorySearchConfig.placeholder"
            @update:search-query="searchQuery = $event"
            @search="searchStudents"
            @page-change="loadStudents"
        >
            <template #item="{ item: student }">
                <StudentListCard :student="student" @select="onSelect" />
            </template>
        </StudentListSection>
    </RpgCard>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import type { StudentListItem } from '@/types/studentList';
import {
    studentDirectoryMessages,
    studentDirectoryPageConfig,
    studentDirectorySearchConfig,
} from '@/constants/studentDirectory';
import { useStudentDirectory } from '@/composables/useStudentDirectory';
import RpgCard from '@/components/rpg/RpgCard.vue';
import StudentListCard from '@/components/rpg/StudentListCard.vue';
import StudentListSection from '@/components/rpg/StudentListSection.vue';

const router = useRouter();
const {
    students,
    meta,
    searchQuery,
    isLoading,
    error,
    emptyMessage,
    loadStudents,
    searchStudents,
} = useStudentDirectory();

const onSelect = (student: StudentListItem): void => {
    void router.push({
        name: 'student-detail',
        params: { studentId: student.id },
    });
};
</script>
