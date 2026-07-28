<template>
    <MentorPanel :config="mentorStudentRegisterPageConfig">
        <MentorStudentRegisterPanel @created="handleCreated" />
        <ToastNotice :message="toastMessage" :show="showToast" />
    </MentorPanel>
</template>

<script setup lang="ts">
import { mentorStudentRegisterPageConfig } from '@/constants/mentor';
import { useMentorRegisterPage } from '@/composables/useMentorRegisterPage';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import MentorStudentRegisterPanel from '@/components/rpg/MentorStudentRegisterPanel.vue';
import ToastNotice from '@/components/rpg/ToastNotice.vue';

const { showToast, toastMessage, handleCreated } = useMentorRegisterPage();
</script>
