<template>
    <article class="badge-item" :class="{ 'is-earned': badge.isEarned }">
        <div class="badge-icon" :title="badge.description || badge.title">
            <i :class="badge.icon"></i>
        </div>
        <p class="badge-title">{{ badge.title }}</p>
    </article>
</template>

<script setup lang="ts">
import type { BadgeItem } from '@/types/badge/badge';

defineProps<{
    badge: BadgeItem;
}>();
</script>
