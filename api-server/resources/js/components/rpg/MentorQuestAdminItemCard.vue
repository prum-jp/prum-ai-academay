<template>
    <article class="quest-item mentor-quest-admin-item">
        <div class="quest-body mentor-quest-admin-body">
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

            <p v-if="quest.rewardText" class="quest-reward">
                <i class="fa-solid fa-star"></i>
                {{ quest.rewardText }}
            </p>

            <p class="quest-participants">
                参加人数: {{ quest.participantCount }}人
            </p>
        </div>

        <div class="mentor-quest-admin-actions">
            <MentorPublishToggle
                :model-value="quest.isPublished"
                :disabled="disabled"
                @update:model-value="$emit('toggle-publish')"
            />
            <button
                type="button"
                class="mentor-quest-admin-action"
                :disabled="disabled"
                @click="$emit('edit')"
            >
                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                {{ mentorQuestAdminCardActions.editLabel }}
            </button>
            <button
                type="button"
                class="mentor-quest-admin-action is-delete"
                :disabled="disabled"
                @click="$emit('delete')"
            >
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                {{ mentorQuestAdminCardActions.deleteLabel }}
            </button>
        </div>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { MentorQuestItem } from '@/types/questAdmin';
import { mentorQuestAdminCardActions } from '@/constants/questAdmin';
import MentorPublishToggle from '@/components/rpg/MentorPublishToggle.vue';

const props = defineProps<{
    quest: MentorQuestItem;
    disabled?: boolean;
}>();

defineEmits<{
    edit: [];
    delete: [];
    'toggle-publish': [];
}>();

const badgeVariant = computed((): string => {
    if (props.quest.type === 'special') {
        return 'is-welcome';
    }

    if (props.quest.unlockLevel !== null) {
        return 'is-lock';
    }

    return 'is-default';
});
</script>
