<template>
    <button
        v-if="showAssignAll"
        type="button"
        class="mentor-quest-admin-action"
        :disabled="disabled"
        @click="$emit('assign-all')"
    >
        <i class="fa-solid fa-users" aria-hidden="true"></i>
        {{ mentorAssignmentMessages.assignAllStudents }}
    </button>
    <button
        v-if="showEdit"
        type="button"
        class="mentor-quest-admin-action"
        :disabled="disabled"
        @click="$emit('edit')"
    >
        <i class="fa-solid fa-pen" aria-hidden="true"></i>
        {{ editLabel }}
    </button>
    <button
        type="button"
        class="mentor-quest-admin-action is-delete"
        :disabled="disabled"
        @click="$emit('delete')"
    >
        <i class="fa-solid fa-trash" aria-hidden="true"></i>
        {{ deleteLabel }}
    </button>
</template>

<script setup lang="ts">
import { mentorAssignmentMessages } from '@/constants/mentor-quest/curriculum';

withDefaults(
    defineProps<{
        disabled?: boolean;
        editLabel: string;
        deleteLabel: string;
        showAssignAll?: boolean;
        showEdit?: boolean;
    }>(),
    {
        disabled: false,
        showAssignAll: true,
        showEdit: true,
    },
);

defineEmits<{
    edit: [];
    delete: [];
    'assign-all': [];
}>();
</script>
