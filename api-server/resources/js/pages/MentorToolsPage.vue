<template>
    <MentorPanel :config="mentorToolPageConfig">
        <MentorToolBoard @notify="showNotice" />
        <ToastNotice :message="toastMessage" :show="showToast" />
    </MentorPanel>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { mentorToolPageConfig } from '@/constants/toolAdmin';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import MentorToolBoard from '@/components/rpg/MentorToolBoard.vue';
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
