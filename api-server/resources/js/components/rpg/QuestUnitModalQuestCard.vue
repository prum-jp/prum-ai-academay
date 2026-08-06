<template>
    <article
        class="quest-unit-modal-row"
        :class="{
            'is-completed': !quest.isLocked && quest.progressStatus === 'completed',
            'is-locked': quest.isLocked,
        }"
    >
        <div class="quest-unit-modal-row-main">
            <RouterLink
                v-if="!quest.isLocked"
                class="quest-unit-modal-row-title"
                :to="{ name: 'student-quest-detail', params: { questId: quest.id } }"
            >
                {{ quest.title }}
            </RouterLink>
            <span v-else class="quest-unit-modal-row-title is-disabled">
                {{ quest.title }}
            </span>
            <span v-if="quest.isLocked" class="quest-unit-modal-lock-note">
                {{ lockLabel }}
            </span>
        </div>

        <QuestProgressStatusBadge
            v-if="!quest.isLocked"
            :status="quest.progressStatus"
            size="sm"
            compact
        />
        <span v-else class="quest-progress-badge is-sm is-not-started">未解放</span>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import type { QuestItem } from '@/types/quest';
import { formatQuestLockLabel } from '@/utils/questDisplay';
import QuestProgressStatusBadge from '@/components/rpg/QuestProgressStatusBadge.vue';

const props = defineProps<{
    quest: QuestItem;
}>();

const lockLabel = computed(() => formatQuestLockLabel(props.quest));
</script>
