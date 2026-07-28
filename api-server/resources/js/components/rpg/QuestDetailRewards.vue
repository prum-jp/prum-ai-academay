<template>
    <QuestDetailSection :title="section.title" :icon="section.icon">
        <p v-if="rewardText" class="quest-reward">
            <i class="fa-solid fa-gift"></i>
            {{ rewardText }}
        </p>
        <ul v-if="rewards.length > 0" class="quest-detail-rewards">
            <li v-for="(reward, index) in rewards" :key="index">
                <i class="fa-solid fa-bolt"></i>
                {{ formatQuestReward(reward) }}
            </li>
        </ul>
        <p v-else-if="!rewardText" class="quest-detail-empty">
            {{ emptyMessage }}
        </p>
    </QuestDetailSection>
</template>

<script setup lang="ts">
import type { QuestReward } from '@/types/quest';
import { formatQuestReward } from '@/utils/questDisplay';
import QuestDetailSection from '@/components/rpg/QuestDetailSection.vue';

defineProps<{
    section: {
        title: string;
        icon: string;
    };
    rewardText: string;
    rewards: QuestReward[];
    emptyMessage: string;
}>();
</script>
