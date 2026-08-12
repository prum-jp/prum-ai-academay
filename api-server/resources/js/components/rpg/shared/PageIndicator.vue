<template>
    <div class="page-indicator">
        <span class="page-indicator-label">{{ label }}</span>
        <div class="page-dots">
            <span
                v-for="index in total"
                :key="index"
                class="page-dot"
                :class="{ active: index - 1 === currentIndex }"
                :title="titles?.[index - 1]"
            ></span>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    label: string;
    total: number;
    currentIndex: number;
    titles?: string[];
}>();
</script>
