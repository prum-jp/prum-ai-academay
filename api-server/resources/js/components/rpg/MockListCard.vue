<template>
    <RpgCard :title="title" :icon="icon">
        <ul class="mock-list">
            <li v-for="(item, index) in items" :key="index">{{ item }}</li>
        </ul>
        <p v-if="note" class="mock-note">{{ note }}</p>
    </RpgCard>
</template>

<script setup lang="ts">
import RpgCard from './RpgCard.vue';

defineProps<{
    title: string;
    icon: string;
    items: string[];
    note?: string;
}>();
</script>
