<template>
    <GameWindow
        :title="config.title"
        :subtitle="mentorPanelConfig.subtitle"
        :icon="config.icon"
        single-column
    >
        <slot />
    </GameWindow>
</template>

<script setup lang="ts">
import { mentorPanelConfig } from '@/constants/mentor';
import GameWindow from '@/components/rpg/GameWindow.vue';

defineProps<{
    config: {
        title: string;
        icon: string;
    };
}>();
</script>
