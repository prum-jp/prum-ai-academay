<template>
    <MentorPanel :config="mentorStudentRegisterPageConfig">
        <MentorStudentRegisterPanel @created="handleCreated" />
        <ToastNotice :message="toastMessage" :show="showToast" />
    </MentorPanel>
</template>

<script setup lang="ts">
import { mentorStudentRegisterPageConfig } from '@/constants/mentor/mentor';
import { useMentorRegisterPage } from '@/composables/mentor/useMentorRegisterPage';
import MentorPanel from '@/components/rpg/mentor/MentorPanel.vue';
import MentorStudentRegisterPanel from '@/components/rpg/mentor/MentorStudentRegisterPanel.vue';
import ToastNotice from '@/components/rpg/shared/ToastNotice.vue';

const { showToast, toastMessage, handleCreated } = useMentorRegisterPage();
</script>
