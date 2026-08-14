<template>
    <ol class="mentor-quest-master-quest-list">
        <li v-for="quest in quests" :key="quest.id" class="mentor-quest-master-quest-item">
            <span class="mentor-quest-master-quest-no">{{ quest.sortOrder }}</span>
            <span class="mentor-quest-master-quest-title">{{ quest.title }}</span>
            <MentorRowActionMenu
                :detail-to="mentorQuestMasterQuestDetailRoute(quest.id)"
                :edit-to="mentorQuestMasterQuestEditRoute(quest.id)"
                @delete="emit('delete', quest)"
            />
        </li>
    </ol>
</template>

<script setup lang="ts">
import type { QuestMasterQuestRow } from '@/types/mentor-master/questMaster';
import {
    mentorQuestMasterQuestDetailRoute,
    mentorQuestMasterQuestEditRoute,
} from '@/utils/mentor-master/mentorQuestMasterRoutes';
import MentorRowActionMenu from '@/components/rpg/mentor-master/MentorRowActionMenu.vue';

defineProps<{
    quests: QuestMasterQuestRow[];
}>();

const emit = defineEmits<{
    delete: [quest: QuestMasterQuestRow];
}>();
</script>

<style scoped>
.mentor-quest-master-quest-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.mentor-quest-master-quest-item {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid #ddd;
}

.mentor-quest-master-quest-item:last-child {
    border-bottom: none;
}

.mentor-quest-master-quest-no {
    flex: 0 0 auto;
    min-width: 1.5em;
    color: #555;
    font-weight: 900;
    text-align: right;
}

.mentor-quest-master-quest-title {
    flex: 1 1 auto;
    min-width: 0;
}
</style>
