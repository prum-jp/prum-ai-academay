<template>
    <div class="mentor-quest-create-page-actions">
        <slot />

        <RpgButton
            v-if="showSubmit"
            :type="submitType"
            :tone="submitTone"
            :icon="submitIcon"
            :disabled="isSubmitting"
        >
            {{ isSubmitting ? submittingLabel : submitLabel }}
        </RpgButton>

        <RpgButton
            v-if="showCancel"
            type="button"
            tone="red"
            :disabled="isSubmitting"
            @click="$emit('cancel')"
        >
            {{ cancelLabel }}
        </RpgButton>
    </div>
</template>

<script setup lang="ts">
import RpgButton from '@/components/rpg/shared/RpgButton.vue';

withDefaults(
    defineProps<{
        isSubmitting?: boolean;
        submitLabel?: string;
        submittingLabel?: string;
        submitType?: 'button' | 'submit';
        submitTone?: 'default' | 'orange' | 'green' | 'red';
        submitIcon?: string;
        showSubmit?: boolean;
        showCancel?: boolean;
        cancelLabel?: string;
    }>(),
    {
        isSubmitting: false,
        submitLabel: '',
        submittingLabel: '',
        submitType: 'submit',
        submitTone: 'default',
        submitIcon: 'fa-solid fa-floppy-disk',
        showSubmit: true,
        showCancel: false,
        cancelLabel: 'キャンセル',
    },
);

defineEmits<{
    cancel: [];
}>();
</script>
