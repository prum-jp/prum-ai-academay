<template>
    <article class="quest-item quest-unit-item mentor-quest-admin-item">
        <div class="quest-body mentor-quest-admin-body">
            <div class="mentor-quest-admin-unit-title">
                <h4 class="quest-title">{{ unit.title }}</h4>
                <span class="quest-badge is-default">
                    {{ mentorQuestAdminMessages.unitQuestCountLabel(unit.questCount) }}
                </span>
            </div>

            <p v-if="unit.rewardText" class="quest-reward">
                <i class="fa-solid fa-star"></i>
                {{ unit.rewardText }}
            </p>
        </div>

        <div class="mentor-quest-admin-actions">
            <MentorPublishToggle
                :model-value="unit.isPublished"
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
import type { MentorQuestUnitItem } from '@/types/questAdmin';
import {
    mentorQuestAdminCardActions,
    mentorQuestAdminMessages,
} from '@/constants/questAdmin';
import MentorPublishToggle from '@/components/rpg/MentorPublishToggle.vue';

defineProps<{
    unit: MentorQuestUnitItem;
    disabled?: boolean;
}>();

defineEmits<{
    edit: [];
    delete: [];
    'toggle-publish': [];
}>();
</script>
