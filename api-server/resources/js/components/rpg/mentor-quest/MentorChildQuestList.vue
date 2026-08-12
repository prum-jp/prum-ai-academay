<template>
    <section v-if="items.length > 0" class="mentor-quest-create-added-list">
        <h3 class="mentor-quest-create-section-title">
            {{ title }}
            ({{ items.length }})
        </h3>
        <ul class="mentor-quest-create-added-items">
            <li
                v-for="(item, index) in items"
                :key="itemKey(item, index)"
                class="mentor-quest-create-added-item"
            >
                <span class="mentor-quest-create-added-index">{{ item.sortOrder }}</span>

                <RouterLink
                    v-if="item.linkTo"
                    class="mentor-quest-create-added-title mentor-quest-detail-child-link"
                    :to="item.linkTo"
                >
                    {{ item.title }}
                </RouterLink>
                <span v-else class="mentor-quest-create-added-title">{{ item.title }}</span>

                <button
                    v-if="removable"
                    type="button"
                    class="mentor-quest-create-added-remove"
                    :disabled="disabled"
                    @click="$emit('remove', index)"
                >
                    {{ removeLabel }}
                </button>
            </li>
        </ul>
    </section>
</template>

<script setup lang="ts">
import { RouterLink, type RouteLocationRaw } from 'vue-router';

export interface MentorChildQuestListItem {
    sortOrder: number;
    title: string;
    id?: number | null;
    linkTo?: RouteLocationRaw;
}

defineProps<{
    items: MentorChildQuestListItem[];
    title: string;
    removable?: boolean;
    disabled?: boolean;
    removeLabel?: string;
}>();

defineEmits<{
    remove: [index: number];
}>();

const itemKey = (item: MentorChildQuestListItem, index: number): string =>
    item.id !== undefined && item.id !== null ? String(item.id) : `${item.title}-${index}`;
</script>

<script lang="ts">
export default {
    inheritAttrs: false,
};
</script>

<style scoped>
.mentor-quest-detail-child-link {
    color: inherit;
    text-decoration: none;
}

.mentor-quest-detail-child-link:hover {
    color: var(--orange-main);
    text-decoration: underline;
}
</style>
