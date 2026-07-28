<template>
    <button class="btn-rpg" :type="type" :disabled="disabled">
        <i v-if="icon" :class="icon"></i>
        <slot />
    </button>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit';
        icon?: string;
        disabled?: boolean;
    }>(),
    {
        type: 'button',
        icon: '',
        disabled: false,
    },
);
</script>
