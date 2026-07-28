<template>
    <button
        type="button"
        class="mentor-student-card"
        :class="{ 'is-selected': student.isSelected }"
        :disabled="disabled"
        @click="$emit('select', student)"
    >
        <div class="mentor-student-avatar">
            <img v-if="student.avatarUrl" :src="student.avatarUrl" :alt="student.name" />
            <span v-else>HERO</span>
        </div>

        <div class="mentor-student-body">
            <div class="mentor-student-title-row">
                <h3>{{ student.name }}</h3>
            </div>
            <p class="mentor-student-email">{{ student.email }}</p>
            <p class="mentor-student-meta">
                <span>{{ student.levelTitle }}</span>
                <span>バッジ {{ student.earnedBadgeCount }}</span>
            </p>
        </div>
    </button>
</template>

<script setup lang="ts">
import type { MentorStudent } from '@/types/mentor';

defineProps<{
    student: MentorStudent;
    disabled?: boolean;
}>();

defineEmits<{
    select: [student: MentorStudent];
}>();
</script>
