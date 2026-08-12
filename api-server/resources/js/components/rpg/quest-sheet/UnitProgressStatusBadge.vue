<template>
    <span class="quest-progress-badge" :class="[statusClass, sizeClass]">
        {{ displayLabel }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { UnitProgressStatus } from '@/constants/quest/unitProgress';
import { getUnitProgressStatusClass, getUnitProgressStatusLabel } from '@/constants/quest/unitProgress';

const props = withDefaults(
    defineProps<{
        status: UnitProgressStatus;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    {
        size: 'md',
    },
);

const displayLabel = computed(() => getUnitProgressStatusLabel(props.status));
const statusClass = computed(() => getUnitProgressStatusClass(props.status));
const sizeClass = computed(() => `is-${props.size}`);
</script>
