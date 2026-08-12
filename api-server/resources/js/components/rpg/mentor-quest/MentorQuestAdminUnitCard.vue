<template>
    <MentorAdminItemCard article-class="quest-unit-item">
        <div class="mentor-quest-admin-unit-title">
            <h4 class="quest-title">{{ unit.title }}</h4>
            <span class="quest-badge is-default">
                {{ mentorQuestAdminMessages.unitQuestCountLabel(unit.questCount) }}
            </span>
        </div>

        <template #actions>
            <MentorAdminStandardActions
                :disabled="disabled"
                :edit-label="mentorQuestAdminCardActions.editLabel"
                :delete-label="mentorQuestAdminCardActions.deleteLabel"
                @assign-all="$emit('assign-all')"
                @edit="$emit('edit')"
                @delete="$emit('delete')"
            />
        </template>
    </MentorAdminItemCard>
</template>

<script setup lang="ts">
import type { MentorQuestUnitItem } from '@/types/mentor-quest/questAdmin';
import {
    mentorQuestAdminCardActions,
    mentorQuestAdminMessages,
} from '@/constants/mentor-quest/questAdmin';
import MentorAdminItemCard from '@/components/rpg/mentor-quest/MentorAdminItemCard.vue';
import MentorAdminStandardActions from '@/components/rpg/mentor-quest/MentorAdminStandardActions.vue';

defineProps<{
    unit: MentorQuestUnitItem;
    disabled?: boolean;
}>();

defineEmits<{
    edit: [];
    delete: [];
    'assign-all': [];
}>();
</script>
