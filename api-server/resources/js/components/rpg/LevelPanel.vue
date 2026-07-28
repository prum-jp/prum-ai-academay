<template>
    <div class="level-panel">
        <div class="level-badge">{{ levelTitle }}</div>
        <div class="xp-bar-container">
            <div class="xp-bar-fill" :style="{ width: `${progressPercent}%` }"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    levelTitle: string;
    progressPercent: number;
}>();
</script>
