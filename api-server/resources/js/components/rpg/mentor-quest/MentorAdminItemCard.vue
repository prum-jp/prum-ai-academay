<template>
    <article
        class="quest-item mentor-quest-admin-item"
        :class="articleClass"
    >
        <div class="quest-body mentor-quest-admin-body">
            <slot />
        </div>

        <div v-if="$slots.actions" class="mentor-quest-admin-actions">
            <slot name="actions" />
        </div>
    </article>
</template>

<script setup lang="ts">
defineProps<{
    articleClass?: string | Record<string, boolean> | Array<string | Record<string, boolean>>;
}>();
</script>
