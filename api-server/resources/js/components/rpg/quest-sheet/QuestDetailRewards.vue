<template>
    <QuestDetailSection :title="section.title" :icon="section.icon">
        <p v-if="rewardText" class="quest-reward">
            <i class="fa-solid fa-gift"></i>
            {{ rewardText }}
        </p>
        <ul v-if="skillGrants.length > 0" class="quest-detail-rewards">
            <li v-for="(skill, index) in skillGrants" :key="index">
                <i class="fa-solid fa-bolt"></i>
                {{ formatSkillGrantReward(skill) }}
            </li>
        </ul>
        <p v-else-if="!rewardText" class="quest-detail-empty">
            {{ emptyMessage }}
        </p>
    </QuestDetailSection>
</template>

<script setup lang="ts">
import type { SkillKey } from '@/constants/shared/skills';
import { formatSkillGrantReward } from '@/utils/quest/skillGrants';
import QuestDetailSection from '@/components/rpg/quest-sheet/QuestDetailSection.vue';

defineProps<{
    section: {
        title: string;
        icon: string;
    };
    rewardText: string;
    skillGrants: SkillKey[];
    emptyMessage: string;
}>();
</script>
