<template>
    <PageLoadGate
        :is-loading="isLoading"
        :load-error="loadError"
        :loading-message="studentDirectoryMessages.profileLoading"
        @retry="loadProfile"
    >
        <template v-if="profile">
            <QuestSheetBackNav
                class="student-detail-nav"
                :back-to="{ name: 'student-directory' }"
                :back-label="studentDirectoryMessages.backToDirectory"
            >
                <template v-if="nextStudent" #secondary>
                    <RouterLink
                        class="quest-sheet-back-link student-detail-next-link"
                        :to="{ name: 'student-detail', params: { studentId: nextStudent.id } }"
                    >
                        {{ nextStudent.name }}{{ studentDirectoryMessages.nextStudentLinkSuffix }}
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </RouterLink>
                </template>
            </QuestSheetBackNav>

            <AdventurerProfilePanel
                :profile="profile"
                :slack-preview="slackPreview"
                @copy-card="copyAdventurerCard"
            />
        </template>
    </PageLoadGate>

    <ToastNotice message="受講者カードをコピーしました！" :show="showToast" />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { studentDirectoryMessages } from '@/constants/studentDirectory';
import { usePeerStudentProfile } from '@/composables/usePeerStudentProfile';
import AdventurerProfilePanel from '@/components/rpg/AdventurerProfilePanel.vue';
import PageLoadGate from '@/components/rpg/PageLoadGate.vue';
import QuestSheetBackNav from '@/components/rpg/QuestSheetBackNav.vue';
import ToastNotice from '@/components/rpg/ToastNotice.vue';

const route = useRoute();
const studentId = computed(() => Number(route.params.studentId));

const {
    profile,
    nextStudent,
    isLoading,
    loadError,
    slackPreview,
    showToast,
    loadProfile,
    copyAdventurerCard,
} = usePeerStudentProfile(() => studentId.value);
</script>

<style scoped>
.student-detail-nav {
    grid-column: 1 / -1;
    margin: 0 0 12px;
}

.student-detail-next-link {
    margin-left: auto;
    text-align: right;
}
</style>
