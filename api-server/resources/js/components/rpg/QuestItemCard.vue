<template>
    <article class="quest-item" :class="itemClass">
        <button type="button" class="quest-body" @click="$emit('open')">
            <div class="quest-title-row">
                <h4 class="quest-title">{{ quest.title }}</h4>
                <QuestProgressStatusBadge :status="quest.progressStatus" size="sm" compact />
            </div>

            <p v-if="quest.rewardText && !hideReward" class="quest-reward">
                <i class="fa-solid fa-star"></i>
                {{ quest.rewardText }}
            </p>

            <p v-if="showParticipants" class="quest-participants">
                参加人数: {{ quest.participantCount }}人
            </p>
        </button>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { QuestItem } from '@/types/quest';
import { showParticipantCount } from '@/utils/questDisplay';
import QuestProgressStatusBadge from '@/components/rpg/QuestProgressStatusBadge.vue';

const props = withDefaults(
    defineProps<{
        quest: QuestItem;
        hideReward?: boolean;
    }>(),
    {
        hideReward: false,
    },
);

defineEmits<{
    open: [];
}>();

const showParticipants = computed(() => showParticipantCount(props.quest));

const itemClass = computed(() => ({
    'is-locked': props.quest.isLocked,
    'is-completed': props.quest.progressStatus === 'completed',
}));
</script>
