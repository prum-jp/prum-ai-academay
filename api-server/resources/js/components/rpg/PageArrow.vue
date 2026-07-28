<template>
    <button
        class="page-arrow"
        :class="direction === 'left' ? 'page-arrow-left' : 'page-arrow-right'"
        type="button"
        :aria-label="label"
        @click="$emit('click')"
    >
        <i :class="direction === 'left' ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'"></i>
    </button>
</template>

<script setup lang="ts">
defineProps<{
    direction: 'left' | 'right';
    label: string;
}>();

defineEmits<{
    click: [];
}>();
</script>
