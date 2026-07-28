import { computed, type Ref } from 'vue';
import type { QuestItem } from '@/types/quest';
import { questDetailConfig } from '@/constants/quests';
import {
    getQuestBadgeVariant,
    getQuestDetailFacts,
    getQuestStatusClass,
    getQuestStatusLabel,
    getQuestTypeLabel,
} from '@/utils/questDisplay';

export function useQuestDetail(quest: Ref<QuestItem | null>) {
    const badgeVariant = computed(() => {
        if (!quest.value) {
            return 'is-default';
        }
        return getQuestBadgeVariant(quest.value);
    });

    const typeLabel = computed(() => {
        if (!quest.value) {
            return '';
        }
        return getQuestTypeLabel(quest.value.type);
    });

    const statusLabel = computed(() => {
        if (!quest.value) {
            return '';
        }
        return getQuestStatusLabel(quest.value);
    });

    const statusClass = computed(() => {
        if (!quest.value) {
            return '';
        }
        return getQuestStatusClass(quest.value);
    });

    const facts = computed(() => {
        if (!quest.value) {
            return [];
        }
        return getQuestDetailFacts(quest.value);
    });

    const description = computed(() => {
        if (!quest.value?.description) {
            return questDetailConfig.emptyDescription;
        }
        return quest.value.description;
    });

    const hasRewards = computed(() => {
        if (!quest.value) {
            return false;
        }
        return Boolean(quest.value.rewardText) || quest.value.rewards.length > 0;
    });

    return {
        questDetailConfig,
        badgeVariant,
        typeLabel,
        statusLabel,
        statusClass,
        facts,
        description,
        hasRewards,
    };
}
