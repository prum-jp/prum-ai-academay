<template>
    <p class="quest-sheet-section-text">
        <template v-for="(segment, index) in segments" :key="index">
            <a
                v-if="segment.type === 'link'"
                class="quest-sheet-section-link"
                :href="segment.href"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{ segment.label }}
            </a>
            <template v-else>{{ segment.value }}</template>
        </template>
    </p>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { parseQuestSheetBody } from '@/utils/questSheetBody';

const props = defineProps<{
    body: string;
}>();

const segments = computed(() => parseQuestSheetBody(props.body));
</script>
