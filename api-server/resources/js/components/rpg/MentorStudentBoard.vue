<template>
    <RpgCard :title="mentorStudentListCardConfig.title" :icon="mentorStudentListCardConfig.icon">
        <template #title-extra>
            <RouterLink
                class="mentor-register-link"
                :to="{ name: mentorStudentListCardConfig.registerRouteName }"
            >
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                {{ mentorStudentListCardConfig.registerLinkLabel }}
            </RouterLink>
        </template>

        <p class="mentor-message">{{ mentorStudentListCardConfig.description }}</p>

        <div class="mentor-student-list">
            <MentorStudentSearch
                v-model="searchQuery"
                :is-loading="isLoading"
                @search="searchStudents"
            />

            <AsyncState
                :is-loading="isLoading"
                :error="error"
                :is-empty="students.length === 0"
                :loading-message="mentorStudentMessages.loading"
                :empty-message="emptyMessage"
            >
                <MentorStudentCard
                    v-for="student in students"
                    :key="student.id"
                    :student="student"
                    :disabled="isSelecting"
                    @select="onSelect"
                />
            </AsyncState>

            <QuestPagination
                :meta="meta"
                :disabled="isLoading || isSelecting"
                @page-change="loadStudents"
            />

            <p v-if="selectError" class="login-error">{{ selectError }}</p>
        </div>
    </RpgCard>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import type { MentorStudent } from '@/types/mentor';
import {
    mentorStudentListCardConfig,
    mentorStudentMessages,
} from '@/constants/mentor';
import { useMentorStudents } from '@/composables/useMentorStudents';
import AsyncState from '@/components/rpg/AsyncState.vue';
import MentorStudentCard from '@/components/rpg/MentorStudentCard.vue';
import MentorStudentSearch from '@/components/rpg/MentorStudentSearch.vue';
import QuestPagination from '@/components/rpg/QuestPagination.vue';
import RpgCard from '@/components/rpg/RpgCard.vue';

const router = useRouter();
const {
    students,
    meta,
    searchQuery,
    appliedQuery,
    isLoading,
    isSelecting,
    error,
    selectError,
    loadStudents,
    searchStudents,
    selectStudent,
} = useMentorStudents();

const emptyMessage = computed((): string => {
    if (appliedQuery.value !== '') {
        return mentorStudentMessages.emptySearch;
    }

    return mentorStudentMessages.emptyList;
});

const onSelect = async (student: MentorStudent): Promise<void> => {
    const selected = await selectStudent(student);
    if (!selected) {
        return;
    }

    await router.push({ name: 'student-sheet' });
};
</script>
