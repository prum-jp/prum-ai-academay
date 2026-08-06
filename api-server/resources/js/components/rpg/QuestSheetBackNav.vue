<template>
    <p
        class="quest-sheet-back"
        :class="{ 'quest-sheet-back-actions': Boolean($slots.secondary) }"
    >
        <RouterLink class="quest-sheet-back-link" :to="backTo">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            {{ backLabel }}
        </RouterLink>
        <slot name="secondary" />
    </p>
</template>

<script setup lang="ts">
import { RouterLink, type RouteLocationRaw } from 'vue-router';

defineProps<{
    backTo: RouteLocationRaw;
    backLabel: string;
}>();
</script>

<style scoped>
.quest-sheet-back-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
}
</style>
