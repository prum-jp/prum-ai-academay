<template>
    <QuestDetailSection :title="section.title" :icon="section.icon">
        <dl class="quest-detail-facts">
            <div v-for="fact in facts" :key="fact.label">
                <dt>{{ fact.label }}</dt>
                <dd>{{ fact.value }}</dd>
            </div>
        </dl>
    </QuestDetailSection>
</template>

<script setup lang="ts">
import type { QuestDetailFact } from '@/utils/quest/questDisplay';
import QuestDetailSection from '@/components/rpg/quest-sheet/QuestDetailSection.vue';

defineProps<{
    section: {
        title: string;
        icon: string;
    };
    facts: QuestDetailFact[];
}>();
</script>
