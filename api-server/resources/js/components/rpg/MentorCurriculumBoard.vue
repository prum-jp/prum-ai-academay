<template>
    <section class="quest-section mentor-curriculum-board">
        <header class="quest-section-header mentor-curriculum-board-header">
            <div class="mentor-curriculum-board-heading">
                <i :class="mentorCurriculumBoardConfig.icon"></i>
                <h3>{{ mentorCurriculumBoardConfig.title }}</h3>
            </div>
            <button type="button" class="mentor-register-link" @click="openCreate">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                {{ mentorCurriculumMessages.createButtonLabel }}
            </button>
        </header>

        <p class="mentor-message">{{ mentorCurriculumBoardConfig.description }}</p>

        <AsyncState
            :is-loading="isLoading"
            :error="error ?? undefined"
            :is-empty="curricula.length === 0"
            :loading-message="mentorCurriculumMessages.loading"
            :empty-message="mentorCurriculumMessages.emptyList"
        >
            <div class="quest-list">
                <MentorCurriculumAdminCard
                    v-for="curriculum in curricula"
                    :key="curriculum.id"
                    :curriculum="curriculum"
                    :disabled="isDeleting || isAssigningAll"
                    @edit="openEdit(curriculum.id)"
                    @delete="onDelete(curriculum)"
                    @assign-all="onAssignAll(curriculum)"
                />
            </div>
        </AsyncState>

        <QuestPagination
            :meta="meta"
            :disabled="isLoading"
            @page-change="loadCurricula"
        />

        <MentorCurriculumFormModal
            :open="isFormOpen"
            :curriculum-id="editingCurriculumId"
            @close="closeForm"
            @saved="onSaved"
        />
    </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import {
    assignMentorCurriculumToAllStudents,
    deleteMentorCurriculum,
    fetchMentorCurricula,
} from '@/api/curriculum';
import {
    mentorAssignmentMessages,
    mentorCurriculumBoardConfig,
    mentorCurriculumMessages,
} from '@/constants/curriculum';
import type { MentorCurriculumItem } from '@/types/curriculum';
import type { QuestListMeta } from '@/types/quest';
import AsyncState from '@/components/rpg/AsyncState.vue';
import MentorCurriculumAdminCard from '@/components/rpg/MentorCurriculumAdminCard.vue';
import MentorCurriculumFormModal from '@/components/rpg/MentorCurriculumFormModal.vue';
import QuestPagination from '@/components/rpg/QuestPagination.vue';

const emit = defineEmits<{
    notify: [message: string];
}>();

const curricula = ref<MentorCurriculumItem[]>([]);
const meta = ref<QuestListMeta>({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
});
const isLoading = ref(false);
const isDeleting = ref(false);
const isAssigningAll = ref(false);
const error = ref<string | null>(null);
const isFormOpen = ref(false);
const editingCurriculumId = ref<number | null>(null);

const loadCurricula = async (page = 1): Promise<void> => {
    isLoading.value = true;
    error.value = null;

    try {
        const response = await fetchMentorCurricula(page);
        curricula.value = response.data;
        meta.value = response.meta;
    } catch {
        error.value = mentorCurriculumMessages.loadFailed;
    } finally {
        isLoading.value = false;
    }
};

const openCreate = (): void => {
    editingCurriculumId.value = null;
    isFormOpen.value = true;
};

const openEdit = (curriculumId: number): void => {
    editingCurriculumId.value = curriculumId;
    isFormOpen.value = true;
};

const closeForm = (): void => {
    isFormOpen.value = false;
    editingCurriculumId.value = null;
};

const onSaved = async (message: string): Promise<void> => {
    emit('notify', message);
    await loadCurricula(meta.value.currentPage);
};

const onDelete = async (curriculum: MentorCurriculumItem): Promise<void> => {
    if (!window.confirm(`「${curriculum.name}」を削除しますか？`)) {
        return;
    }

    isDeleting.value = true;

    try {
        await deleteMentorCurriculum(curriculum.id);
        emit('notify', mentorCurriculumMessages.deleteSuccess);
        await loadCurricula(meta.value.currentPage);
    } catch {
        emit('notify', mentorCurriculumMessages.deleteFailed);
    } finally {
        isDeleting.value = false;
    }
};

const onAssignAll = async (curriculum: MentorCurriculumItem): Promise<void> => {
    isAssigningAll.value = true;

    try {
        const count = await assignMentorCurriculumToAllStudents(curriculum.id);
        emit('notify', mentorAssignmentMessages.assignAllStudentsSuccess(count));
    } catch {
        emit('notify', mentorAssignmentMessages.assignAllStudentsFailed);
    } finally {
        isAssigningAll.value = false;
    }
};

onMounted(() => {
    void loadCurricula();
});
</script>
