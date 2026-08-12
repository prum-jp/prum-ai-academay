<template>
    <PageLoadGate
        :is-loading="isLoading"
        :load-error="loadError"
        loading-message="プロフィールを読み込んでいます..."
        @retry="loadProfile"
    >
        <AdventurerProfilePanel
            v-if="profile"
            :profile="profile"
            :editable="isStudent"
            :save-status="saveStatus"
            :save-status-label="saveStatusLabel"
            :slack-preview="slackPreview"
            @persist="persistProfile"
            @profile-updated="applyProfileUpdate"
            @copy-card="copyAdventurerCard"
        />
    </PageLoadGate>

    <ToastNotice :message="toastMessage" :show="showToast" />
</template>

<script setup lang="ts">
import { useStudentProfile } from '@/composables/student/useStudentProfile';
import AdventurerProfilePanel from '@/components/rpg/student/AdventurerProfilePanel.vue';
import PageLoadGate from '@/components/rpg/shared/PageLoadGate.vue';
import ToastNotice from '@/components/rpg/shared/ToastNotice.vue';

const {
    profile,
    isLoading,
    loadError,
    saveStatus,
    saveStatusLabel,
    slackPreview,
    showToast,
    toastMessage,
    isStudent,
    loadProfile,
    persistProfile,
    applyProfileUpdate,
    copyAdventurerCard,
} = useStudentProfile();
</script>
