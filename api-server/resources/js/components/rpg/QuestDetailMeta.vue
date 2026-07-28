<template>
    <div class="quest-detail-meta">
        <span class="quest-badge" :class="badgeVariant">
            {{ typeLabel }}
        </span>
        <span
            v-if="badgeLabel"
            class="quest-badge"
            :class="badgeVariant"
        >
            {{ badgeLabel }}
        </span>
        <span class="quest-status-pill" :class="statusClass">
            {{ statusLabel }}
        </span>
    </div>
</template>

<script setup lang="ts">
import type { QuestBadgeVariant, QuestStatusClass } from '@/utils/questDisplay';

defineProps<{
    typeLabel: string;
    badgeLabel?: string | null;
    statusLabel: string;
    badgeVariant: QuestBadgeVariant;
    statusClass: QuestStatusClass | '';
}>();
</script>
