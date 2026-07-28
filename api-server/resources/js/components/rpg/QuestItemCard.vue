<template>
    <article class="quest-item" :class="{ 'is-locked': quest.isLocked, 'is-completed': quest.isCompleted }">
        <QuestCheckbox
            v-if="showCheckbox"
            :checked="quest.isCompleted"
            :disabled="quest.isLocked || disabled"
            @toggle="$emit('toggle')"
        />

        <button type="button" class="quest-body" @click="$emit('open')">
            <div class="quest-title-row">
                <h4 class="quest-title">{{ quest.title }}</h4>
                <span
                    v-if="quest.badgeLabel"
                    class="quest-badge"
                    :class="badgeVariant"
                >
                    {{ quest.badgeLabel }}
                </span>
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
import { getQuestBadgeVariant, showParticipantCount } from '@/utils/questDisplay';
import QuestCheckbox from '@/components/rpg/QuestCheckbox.vue';

const props = withDefaults(
    defineProps<{
        quest: QuestItem;
        showCheckbox?: boolean;
        hideReward?: boolean;
        disabled?: boolean;
    }>(),
    {
        showCheckbox: false,
        hideReward: false,
        disabled: false,
    },
);

defineEmits<{
    toggle: [];
    open: [];
}>();

const badgeVariant = computed(() => getQuestBadgeVariant(props.quest));
const showParticipants = computed(() => showParticipantCount(props.quest));
</script>
