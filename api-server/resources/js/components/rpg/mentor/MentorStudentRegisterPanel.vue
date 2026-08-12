<template>
    <RpgCard :title="mentorStudentRegisterCardConfig.title" :icon="mentorStudentRegisterCardConfig.icon">
        <p class="mentor-message">{{ mentorStudentRegisterCardConfig.description }}</p>
        <MentorStudentRegisterForm @created="emit('created')" />
    </RpgCard>
</template>

<script setup lang="ts">
import { mentorStudentRegisterCardConfig } from '@/constants/mentor/mentor';
import MentorStudentRegisterForm from '@/components/rpg/mentor/MentorStudentRegisterForm.vue';
import RpgCard from '@/components/rpg/shared/RpgCard.vue';

const emit = defineEmits<{
    created: [];
}>();
</script>
