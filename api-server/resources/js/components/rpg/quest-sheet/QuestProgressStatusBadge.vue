<template>
    <span class="quest-progress-badge" :class="[statusClass, sizeClass]">
        {{ displayLabel }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { QuestProgressStatus } from '@/constants/quest/questProgress';
import {
    getQuestProgressStatusClass,
    getQuestProgressStatusLabel,
    getQuestProgressStatusShortLabel,
} from '@/utils/quest/questProgressDisplay';

const props = withDefaults(
    defineProps<{
        status: QuestProgressStatus;
        size?: 'sm' | 'md' | 'lg';
        compact?: boolean;
    }>(),
    {
        size: 'md',
        compact: false,
    },
);

const displayLabel = computed(() =>
    props.compact || props.size === 'sm'
        ? getQuestProgressStatusShortLabel(props.status)
        : getQuestProgressStatusLabel(props.status),
);

const statusClass = computed(() => getQuestProgressStatusClass(props.status));
const sizeClass = computed(() => `is-${props.size}`);
</script>
