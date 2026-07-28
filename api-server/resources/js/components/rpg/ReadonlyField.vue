<template>
    <div class="input-group">
        <label>{{ label }}</label>
        <div class="readonly-value">{{ value }}</div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    label: string;
    value: string;
}>();
</script>
