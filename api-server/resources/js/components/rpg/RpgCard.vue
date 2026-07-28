<template>
    <div class="rpg-card" :class="{ 'card-preview': variant === 'preview' }">
        <div class="rpg-card-title">
            <div class="rpg-card-title-main">
                <i v-if="icon" :class="icon"></i>
                {{ title }}
            </div>
            <div v-if="$slots['title-extra']" class="rpg-card-title-extra">
                <slot name="title-extra" />
            </div>
        </div>
        <slot />
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        icon?: string;
        variant?: 'default' | 'preview';
    }>(),
    {
        icon: '',
        variant: 'default',
    },
);
</script>
