<template>
    <div class="input-group">
        <label :for="`${idPrefix}-title`">{{ mentorQuestUnitFormLabels.title }}</label>
        <input
            :id="`${idPrefix}-title`"
            v-model="form.title"
            type="text"
            required
            maxlength="255"
            :placeholder="placeholders?.title"
            :disabled="disabled"
            :readonly="readonly"
        />
        <p v-if="fieldErrors.title" class="login-error">{{ fieldErrors.title }}</p>
    </div>
</template>

<script setup lang="ts">
import { mentorQuestUnitFormLabels } from '@/constants/mentor-quest/questAdmin';

interface UnitFormLike {
    title: string;
}

withDefaults(
    defineProps<{
        form: UnitFormLike;
        fieldErrors: Record<string, string>;
        idPrefix: string;
        disabled?: boolean;
        readonly?: boolean;
        placeholders?: {
            title?: string;
        };
    }>(),
    {
        disabled: false,
        readonly: false,
        placeholders: undefined,
    },
);
</script>
