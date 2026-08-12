<template>
    <div class="badge-grid">
        <BadgeItemCard v-for="badge in badges" :key="badge.id" :badge="badge" />
    </div>
</template>

<script setup lang="ts">
import type { BadgeItem } from '@/types/badge/badge';
import BadgeItemCard from '@/components/rpg/badge/BadgeItem.vue';

defineProps<{
    badges: BadgeItem[];
}>();
</script>
