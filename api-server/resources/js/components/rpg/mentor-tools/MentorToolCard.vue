<template>
    <div class="mentor-tool-card">
        <h3>{{ tool.name }}</h3>
        <p class="mentor-tool-card-code">{{ tool.code }}</p>
    </div>
</template>

<script setup lang="ts">
import type { MentorTool } from '@/types/mentor-quest/questAdmin';

defineProps<{
    tool: MentorTool;
}>();
</script>
