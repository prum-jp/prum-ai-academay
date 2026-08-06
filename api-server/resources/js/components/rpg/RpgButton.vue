<template>
    <button
        class="btn-rpg"
        :class="toneClass"
        :type="type"
        :disabled="disabled"
    >
        <i v-if="icon" :class="icon"></i>
        <slot />
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        type?: 'button' | 'submit';
        icon?: string;
        disabled?: boolean;
        tone?: 'default' | 'orange' | 'green' | 'red';
    }>(),
    {
        type: 'button',
        icon: '',
        disabled: false,
        tone: 'default',
    },
);

const toneClass = computed((): string | null => {
    if (props.tone === 'default') {
        return null;
    }

    return `is-tone-${props.tone}`;
});
</script>
