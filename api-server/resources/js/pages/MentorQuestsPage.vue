<template>
    <MentorPanel :config="mentorQuestPageConfig">
        <MentorQuestBoard @notify="showNotice" />
        <ToastNotice :message="toastMessage" :show="showToast" />
    </MentorPanel>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { mentorQuestPageConfig } from '@/constants/questAdmin';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import MentorQuestBoard from '@/components/rpg/MentorQuestBoard.vue';
import ToastNotice from '@/components/rpg/ToastNotice.vue';

const showToast = ref(false);
const toastMessage = ref('');

const showNotice = (message: string): void => {
    toastMessage.value = message;
    showToast.value = true;

    window.setTimeout(() => {
        showToast.value = false;
    }, 1200);
};
</script>
