<template>
    <button class="audio-toggle" type="button" @click="$emit('toggle')">
        <i :class="enabled ? 'fa-solid fa-volume-high' : 'fa-solid fa-volume-xmark'"></i>
        効果音: {{ enabled ? 'ON' : 'OFF' }}
    </button>
</template>

<script setup lang="ts">
defineProps<{
    enabled: boolean;
}>();

defineEmits<{
    toggle: [];
}>();
</script>
