<template>
    <div class="game-window" :class="{ 'is-plain-content': plainContent }">
        <header>
            <h1>
                <i :class="icon"></i>
                {{ title }}
            </h1>
            <div class="header-sub">{{ subtitle }}</div>
        </header>

        <div class="content-grid" :class="{ 'content-grid-single': singleColumn }">
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        subtitle: string;
        icon: string;
        singleColumn?: boolean;
        plainContent?: boolean;
    }>(),
    {
        singleColumn: false,
        plainContent: false,
    },
);
</script>
