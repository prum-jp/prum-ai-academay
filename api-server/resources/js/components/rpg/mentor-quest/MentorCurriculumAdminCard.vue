<template>
    <MentorAdminItemCard article-class="quest-unit-item">
        <h4 class="quest-title">{{ curriculum.name }}</h4>
        <p v-if="curriculum.description" class="quest-reward">{{ curriculum.description }}</p>
        <p class="mentor-assignment-meta">
            {{ mentorQuestAdminMessages.unitQuestCountLabel(curriculum.unitCount) }}
        </p>

        <template #actions>
            <MentorAdminStandardActions
                :disabled="disabled"
                :edit-label="mentorCurriculumMessages.editButtonLabel"
                :delete-label="mentorCurriculumMessages.deleteButtonLabel"
                @assign-all="$emit('assign-all')"
                @edit="$emit('edit')"
                @delete="$emit('delete')"
            />
        </template>
    </MentorAdminItemCard>
</template>

<script setup lang="ts">
import type { MentorCurriculumItem } from '@/types/mentor-quest/curriculum';
import { mentorCurriculumMessages } from '@/constants/mentor-quest/curriculum';
import { mentorQuestAdminMessages } from '@/constants/mentor-quest/questAdmin';
import MentorAdminItemCard from '@/components/rpg/mentor-quest/MentorAdminItemCard.vue';
import MentorAdminStandardActions from '@/components/rpg/mentor-quest/MentorAdminStandardActions.vue';

defineProps<{
    curriculum: MentorCurriculumItem;
    disabled?: boolean;
}>();

defineEmits<{
    edit: [];
    delete: [];
    'assign-all': [];
}>();
</script>
