<template>
    <RpgModal
        :open="open"
        :title="mentorQuestCreateModalConfig.title"
        :icon="mentorQuestCreateModalConfig.icon"
        wide
        @close="handleClose"
    >
        <template #header-actions>
            <button type="button" class="rpg-modal-header-link" @click="isBulkImportOpen = true">
                {{ questImportModalConfig.bulkButtonLabel }}
            </button>
        </template>
        <form class="mentor-quest-create-form" @submit.prevent="onSubmit">
            <div class="input-group">
                <label for="quest-create-type">{{ mentorQuestCreateModalConfig.typeLabel }}</label>
                <select
                    id="quest-create-type"
                    :value="createType"
                    :disabled="isSubmitting"
                    @change="onTypeChange"
                >
                    <option
                        v-for="option in mentorQuestCreateTypeOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <MentorUnitFormFields
                v-if="createType === 'personal'"
                :form="unitForm"
                :field-errors="fieldErrors"
                id-prefix="unit"
                :disabled="isSubmitting"
                :placeholders="{
                    title: mentorQuestFormPlaceholders.unitTitle,
                    description: mentorQuestFormPlaceholders.description,
                    rewardText: mentorQuestFormPlaceholders.rewardText,
                }"
            />

            <MentorQuestFormFields
                v-else
                :form="questForm"
                :field-errors="fieldErrors"
                id-prefix="quest"
                :disabled="isSubmitting"
                :placeholders="{
                    title: mentorQuestFormPlaceholders.questTitle,
                    description: mentorQuestFormPlaceholders.description,
                    clearCondition: mentorQuestFormPlaceholders.clearCondition,
                    rewardText: mentorQuestFormPlaceholders.rewardText,
                    badgeLabel: mentorQuestFormPlaceholders.badgeLabel,
                    unlockLevel: mentorQuestFormPlaceholders.unlockLevel,
                }"
            />

            <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>
        </form>

        <template #footer>
            <div class="mentor-quest-create-actions">
                <RpgButton type="button" :disabled="isSubmitting" @click="handleClose">
                    {{ mentorQuestCreateModalConfig.cancelLabel }}
                </RpgButton>
                <RpgButton
                    type="button"
                    icon="fa-solid fa-plus"
                    :disabled="isSubmitting"
                    @click="onSubmit"
                >
                    {{
                        isSubmitting
                            ? mentorQuestCreateModalConfig.submittingLabel
                            : mentorQuestCreateModalConfig.submitLabel
                    }}
                </RpgButton>
            </div>
        </template>
    </RpgModal>

    <MentorQuestBulkImportModal
        :open="isBulkImportOpen"
        @close="isBulkImportOpen = false"
        @imported="emit('imported')"
    />
</template>

<script setup lang="ts">
import {
    mentorQuestCreateModalConfig,
    mentorQuestCreateTypeOptions,
    mentorQuestFormPlaceholders,
} from '@/constants/questAdmin';
import { questImportModalConfig } from '@/constants/questImport';
import type { MentorQuestCreateType } from '@/types/questAdmin';
import { useMentorQuestCreate } from '@/composables/useMentorQuestCreate';
import MentorQuestBulkImportModal from '@/components/rpg/MentorQuestBulkImportModal.vue';
import MentorQuestFormFields from '@/components/rpg/MentorQuestFormFields.vue';
import MentorUnitFormFields from '@/components/rpg/MentorUnitFormFields.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import RpgModal from '@/components/rpg/RpgModal.vue';
import { ref } from 'vue';

defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
    created: [kind: 'unit' | 'quest'];
    imported: [];
}>();

const isBulkImportOpen = ref(false);

const {
    createType,
    unitForm,
    questForm,
    isSubmitting,
    errorMessage,
    fieldErrors,
    setCreateType,
    submit,
    resetForms,
} = useMentorQuestCreate();

const onTypeChange = (event: Event): void => {
    const value = (event.target as HTMLSelectElement).value as MentorQuestCreateType;
    setCreateType(value);
};

const handleClose = (): void => {
    resetForms();
    emit('close');
};

const onSubmit = async (): Promise<void> => {
    const created = await submit();
    if (!created) {
        return;
    }

    emit('created', createType.value === 'personal' ? 'unit' : 'quest');
};
</script>
