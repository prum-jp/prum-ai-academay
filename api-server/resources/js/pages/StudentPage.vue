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

    <ToastNotice message="受講者カードをコピーしました！" :show="showToast" />
</template>

<script setup lang="ts">
import { useStudentProfile } from '@/composables/useStudentProfile';
import AdventurerProfilePanel from '@/components/rpg/AdventurerProfilePanel.vue';
import PageLoadGate from '@/components/rpg/PageLoadGate.vue';
import ToastNotice from '@/components/rpg/ToastNotice.vue';

const {
    profile,
    isLoading,
    loadError,
    saveStatus,
    saveStatusLabel,
    slackPreview,
    showToast,
    isStudent,
    loadProfile,
    persistProfile,
    applyProfileUpdate,
    copyAdventurerCard,
} = useStudentProfile();
</script>
