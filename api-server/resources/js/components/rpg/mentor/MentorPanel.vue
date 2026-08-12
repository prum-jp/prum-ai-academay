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
import { mentorPanelConfig } from '@/constants/mentor/mentor';
import GameWindow from '@/components/rpg/shared/GameWindow.vue';

defineProps<{
    config: {
        title: string;
        icon: string;
    };
}>();
</script>
