<template>
    <article
        class="mentor-student-card"
        :class="{ 'is-selected': student.isSelected, 'is-directory-only': !showAssign }"
    >
        <button
            type="button"
            class="mentor-student-card-main"
            :disabled="disabled"
            @click="$emit('select', student)"
        >
            <div class="mentor-student-avatar">
                <img v-if="student.avatarUrl" :src="student.avatarUrl" :alt="student.name" />
                <span v-else>{{ avatarConfig.placeholderLabel }}</span>
            </div>

            <div class="mentor-student-body">
                <div class="mentor-student-title-row">
                    <h3>{{ student.name }}</h3>
                </div>
                <p v-if="showEmail && student.email" class="mentor-student-email">
                    {{ student.email }}
                </p>
                <p class="mentor-student-meta">
                    <span>{{ student.levelTitle }}</span>
                    <!-- TODO: 後に機能追加 — 獲得バッジ数表示 -->
                    <!-- <span>バッジ {{ student.earnedBadgeCount }}</span> -->
                </p>
            </div>
        </button>

        <button
            v-if="showAssign"
            type="button"
            class="mentor-student-assignment-btn"
            :disabled="disabled"
            @click="$emit('assign', student)"
        >
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            {{ assignLabel }}
        </button>
    </article>
</template>

<script setup lang="ts">
import type { StudentListItem } from '@/types/student/studentList';
import { avatarConfig } from '@/constants/profile/avatar';
import { mentorStudentAssignmentCardConfig } from '@/constants/mentor-quest/curriculum';

withDefaults(
    defineProps<{
        student: StudentListItem;
        disabled?: boolean;
        showEmail?: boolean;
        showAssign?: boolean;
        assignLabel?: string;
    }>(),
    {
        disabled: false,
        showEmail: false,
        showAssign: false,
        assignLabel: mentorStudentAssignmentCardConfig.buttonLabel,
    },
);

defineEmits<{
    select: [student: StudentListItem];
    assign: [student: StudentListItem];
}>();
</script>

<style scoped>
.mentor-student-card.is-directory-only {
    grid-template-columns: minmax(0, 1fr);
}
</style>
