<template>
    <article class="quest-item quest-unit-item mentor-quest-admin-item">
        <div class="quest-body mentor-quest-admin-body">
            <h4 class="quest-title">{{ curriculum.name }}</h4>
            <p v-if="curriculum.description" class="quest-reward">{{ curriculum.description }}</p>
            <p class="mentor-assignment-meta">
                {{ mentorQuestAdminMessages.unitQuestCountLabel(curriculum.unitCount) }}
            </p>
        </div>

        <div class="mentor-quest-admin-actions">
            <button
                type="button"
                class="mentor-quest-admin-action"
                :disabled="disabled"
                @click="$emit('assign-all')"
            >
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                {{ mentorAssignmentMessages.assignAllStudents }}
            </button>
            <button
                type="button"
                class="mentor-quest-admin-action"
                :disabled="disabled"
                @click="$emit('edit')"
            >
                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                {{ mentorCurriculumMessages.editButtonLabel }}
            </button>
            <button
                type="button"
                class="mentor-quest-admin-action is-delete"
                :disabled="disabled"
                @click="$emit('delete')"
            >
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                {{ mentorCurriculumMessages.deleteButtonLabel }}
            </button>
        </div>
    </article>
</template>

<script setup lang="ts">
import type { MentorCurriculumItem } from '@/types/curriculum';
import {
    mentorAssignmentMessages,
    mentorCurriculumMessages,
} from '@/constants/curriculum';
import { mentorQuestAdminMessages } from '@/constants/questAdmin';

defineProps<{
    curriculum: MentorCurriculumItem;
    disabled?: boolean;
}>();

defineEmits<{
    edit: [];
    delete: [];
    'assign-all': [];
}>();
</script>
