<template>
    <RpgCard :title="title" :icon="icon">
        <p :class="messageClass">{{ message }}</p>
        <div v-if="showRetry" class="action-area">
            <RpgButton icon="fa-solid fa-rotate-right" @click="$emit('retry')">
                再読み込み
            </RpgButton>
        </div>
    </RpgCard>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import RpgCard from '@/components/rpg/shared/RpgCard.vue';
import RpgButton from '@/components/rpg/shared/RpgButton.vue';

const props = withDefaults(
    defineProps<{
        title: string;
        icon: string;
        message: string;
        variant?: 'loading' | 'error';
        showRetry?: boolean;
    }>(),
    {
        variant: 'loading',
        showRetry: false,
    },
);

defineEmits<{
    retry: [];
}>();

const messageClass = computed(() => {
    return props.variant === 'error' ? 'login-error' : 'mock-note';
});
</script>
