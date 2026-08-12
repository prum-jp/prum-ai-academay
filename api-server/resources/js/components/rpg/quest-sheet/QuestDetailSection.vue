<template>
    <section class="quest-detail-section">
        <h3>
            <i :class="icon"></i>
            {{ title }}
        </h3>
        <slot />
    </section>
</template>

<script setup lang="ts">
defineProps<{
    title: string;
    icon: string;
}>();
</script>
