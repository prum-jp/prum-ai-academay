<template>
    <RpgStatusCard
        v-if="isLoading"
        :title="questSheetConfig.loading"
        icon="fa-solid fa-spinner"
        :message="questSheetConfig.loading"
    />

    <RpgStatusCard
        v-else-if="loadError"
        title="取得失敗"
        icon="fa-solid fa-triangle-exclamation"
        variant="error"
        :message="loadError"
        show-retry
        @retry="loadQuest"
    />

    <div v-else-if="quest" class="quest-sheet-page">
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
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { questSheetConfig } from '@/constants/questSheet';
import { useStudentQuestSheetPage } from '@/composables/useStudentQuestSheetPage';
import RpgStatusCard from '@/components/rpg/RpgStatusCard.vue';
import QuestProgressStatusSelect from '@/components/rpg/QuestProgressStatusSelect.vue';
import QuestSheetLayout from '@/components/rpg/QuestSheetLayout.vue';
import QuestSheetContentSections from '@/components/rpg/QuestSheetContentSections.vue';
import QuestSheetComments from '@/components/rpg/QuestSheetComments.vue';

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
