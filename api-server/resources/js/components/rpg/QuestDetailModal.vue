<template>
    <RpgModal
        :open="Boolean(quest)"
        :title="quest?.title ?? questDetailConfig.fallbackTitle"
        :icon="questDetailConfig.modalIcon"
        @close="$emit('close')"
    >
        <template v-if="quest">
            <div class="quest-detail">
                <QuestDetailMeta
                    :type-label="typeLabel"
                    :badge-label="quest.badgeLabel"
                    :status-label="statusLabel"
                    :badge-variant="badgeVariant"
                    :status-class="statusClass"
                />

                <QuestDetailSection
                    :title="questDetailConfig.sections.content.title"
                    :icon="questDetailConfig.sections.content.icon"
                >
                    <p class="quest-detail-description">
                        {{ description }}
                    </p>
                </QuestDetailSection>

                <QuestDetailRewards
                    v-if="hasRewards"
                    :section="questDetailConfig.sections.rewards"
                    :reward-text="quest.rewardText"
                    :rewards="quest.rewards"
                    :empty-message="questDetailConfig.emptyRewards"
                />

                <QuestDetailFacts
                    :section="questDetailConfig.sections.facts"
                    :facts="facts"
                />
            </div>
        </template>

        <template #footer>
            <RpgButton icon="fa-solid fa-door-closed" @click="$emit('close')">
                {{ questDetailConfig.closeLabel }}
            </RpgButton>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import type { QuestItem } from '@/types/quest';
import { useQuestDetail } from '@/composables/useQuestDetail';
import RpgModal from '@/components/rpg/RpgModal.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import QuestDetailMeta from '@/components/rpg/QuestDetailMeta.vue';
import QuestDetailSection from '@/components/rpg/QuestDetailSection.vue';
import QuestDetailRewards from '@/components/rpg/QuestDetailRewards.vue';
import QuestDetailFacts from '@/components/rpg/QuestDetailFacts.vue';

const props = defineProps<{
    quest: QuestItem | null;
}>();

defineEmits<{
    close: [];
}>();

const {
    questDetailConfig,
    badgeVariant,
    typeLabel,
    statusLabel,
    statusClass,
    facts,
    description,
    hasRewards,
} = useQuestDetail(toRef(props, 'quest'));
</script>
