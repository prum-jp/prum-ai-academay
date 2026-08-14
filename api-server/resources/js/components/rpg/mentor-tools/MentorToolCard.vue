<template>
    <div class="mentor-tool-card">
        <h3>{{ tool.name }}</h3>
        <button
            type="button"
            class="mentor-tool-card-edit"
            :aria-label="`${tool.name}を${mentorToolFormModalConfig.editButtonLabel}`"
            @click="emit('edit', tool)"
        >
            <i class="fa-solid fa-pen" aria-hidden="true"></i>
            {{ mentorToolFormModalConfig.editButtonLabel }}
        </button>
    </div>
</template>

<script setup lang="ts">
import type { MentorTool } from '@/types/mentor-quest/questAdmin';
import { mentorToolFormModalConfig } from '@/constants/mentor-tools/toolAdmin';

defineProps<{
    tool: MentorTool;
}>();

const emit = defineEmits<{
    edit: [tool: MentorTool];
}>();
</script>
