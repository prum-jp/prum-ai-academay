<template>
    <PageLoadGate
        :is-loading="isLoading"
        :load-error="loadError"
        :loading-message="questSheetConfig.loading"
        @retry="loadQuest"
    >
        <div v-if="quest" class="quest-sheet-page">
            <p class="quest-sheet-back">
                <RouterLink class="quest-sheet-back-link" :to="{ name: 'student-quests' }">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    {{ questSheetConfig.backToNotebook }}
                </RouterLink>
            </p>

            <QuestSheetLayout
                :title="quest.title"
                :quest-no="questNo"
                :meta-rows="metaRows"
            >
                <template #quest-status>
                    <QuestProgressStatusSelect
                        :status="quest.progressStatus"
                        :is-locked="quest.isLocked"
                        :is-updating="isUpdating"
                        :role="isMentor ? 'mentor' : 'student'"
                        @update="updateStatus"
                    />
                </template>

                <QuestSheetContentSections
                    :sections="sections"
                    :quest-id="quest.id"
                    :submission="quest.submission"
                    :is-locked="quest.isLocked"
                    @submission-saved="onSubmissionSaved"
                />
            </QuestSheetLayout>

            <QuestSheetComments
                :items="commentItems"
                :is-loading="isCommentsLoading"
                :load-error="commentsLoadError"
                :is-locked="quest.isLocked"
                :is-mentor="isMentor"
                :on-post="postComment"
            />
        </div>
    </PageLoadGate>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import { useStudentQuestSheetPage } from '@/composables/student/useStudentQuestSheetPage';
import PageLoadGate from '@/components/rpg/shared/PageLoadGate.vue';
import QuestProgressStatusSelect from '@/components/rpg/quest-sheet/QuestProgressStatusSelect.vue';
import QuestSheetLayout from '@/components/rpg/quest-sheet/QuestSheetLayout.vue';
import QuestSheetContentSections from '@/components/rpg/quest-sheet/QuestSheetContentSections.vue';
import QuestSheetComments from '@/components/rpg/quest-sheet/QuestSheetComments.vue';

const {
    quest,
    isLoading,
    loadError,
    isMentor,
    isUpdating,
    questNo,
    metaRows,
    sections,
    commentItems,
    isCommentsLoading,
    commentsLoadError,
    postComment,
    loadQuest,
    updateStatus,
    onSubmissionSaved,
} = useStudentQuestSheetPage();
</script>
