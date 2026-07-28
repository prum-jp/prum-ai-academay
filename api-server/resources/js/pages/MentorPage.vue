<template>
    <MentorPanel :config="mentorStudentListPageConfig">
        <MentorStudentBoard />
    </MentorPanel>
</template>

<script setup lang="ts">
import { mentorStudentListPageConfig } from '@/constants/mentor';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import MentorStudentBoard from '@/components/rpg/MentorStudentBoard.vue';
</script>
