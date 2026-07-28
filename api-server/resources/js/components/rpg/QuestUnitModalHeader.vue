<template>
    <header class="quest-unit-modal-header">
        <h2 class="quest-unit-modal-title">{{ unitTitle }}</h2>
        <p v-if="growthStatusText" class="quest-unit-growth-badge">
            <i class="fa-solid fa-medal" aria-hidden="true"></i>
            {{ growthStatusText }}
        </p>
        <hr class="quest-unit-modal-divider" />
    </header>
</template>

<script setup lang="ts">
defineProps<{
    unitTitle: string;
    growthStatusText: string;
}>();
</script>
