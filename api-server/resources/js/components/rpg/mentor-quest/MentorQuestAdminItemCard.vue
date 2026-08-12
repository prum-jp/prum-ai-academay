<template>
    <MentorAdminItemCard>
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

        <template #actions>
            <MentorPublishToggle
                :model-value="quest.isPublished"
                :disabled="disabled"
                @update:model-value="$emit('toggle-publish')"
            />
            <MentorAdminStandardActions
                :disabled="disabled"
                :edit-label="mentorQuestAdminCardActions.editLabel"
                :delete-label="mentorQuestAdminCardActions.deleteLabel"
                :show-assign-all="false"
                :show-edit="false"
                @delete="$emit('delete')"
            />
        </template>
    </MentorAdminItemCard>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { MentorQuestItem } from '@/types/mentor-quest/questAdmin';
import { mentorQuestAdminCardActions } from '@/constants/mentor-quest/questAdmin';
import MentorAdminItemCard from '@/components/rpg/mentor-quest/MentorAdminItemCard.vue';
import MentorAdminStandardActions from '@/components/rpg/mentor-quest/MentorAdminStandardActions.vue';
import MentorPublishToggle from '@/components/rpg/mentor-quest/MentorPublishToggle.vue';

const props = defineProps<{
    quest: MentorQuestItem;
    disabled?: boolean;
}>();

defineEmits<{
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
