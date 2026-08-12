<template>
    <MentorPanel :config="mentorToolPageConfig">
        <MentorToolBoard @notify="showNotice" />
        <ToastNotice :message="toastMessage" :show="showToast" />
    </MentorPanel>
</template>

<script setup lang="ts">
import { mentorToolPageConfig } from '@/constants/mentor-tools/toolAdmin';
import { useToastNotice } from '@/composables/shared/useToastNotice';
import MentorPanel from '@/components/rpg/mentor/MentorPanel.vue';
import MentorToolBoard from '@/components/rpg/mentor-tools/MentorToolBoard.vue';
import ToastNotice from '@/components/rpg/shared/ToastNotice.vue';

const { showToast, toastMessage, showNotice } = useToastNotice();
</script>
