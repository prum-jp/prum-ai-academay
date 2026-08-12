<template>
    <div
        class="mentor-quest-create-sheet-form"
        :class="{ 'mentor-quest-create-child-quest': childQuestStyle }"
    >
        <h3 v-if="sectionTitle" class="mentor-quest-create-section-title">
            {{ sectionTitle }}
        </h3>

        <QuestSheetLayout :quest-no="questNo">
            <template #title>
                <slot name="title">
                    <QuestSheetPersonalTitleField
                        :id="titleId"
                        v-model="title"
                        :placeholder="titlePlaceholder"
                        :disabled="disabled"
                        :readonly="titleReadonly"
                        :required="titleRequired"
                    />
                </slot>
            </template>

            <template #quest-status>
                <slot name="quest-status">
                    <span class="quest-sheet-create-type-badge">{{ statusLabel }}</span>
                </slot>
            </template>

            <template #meta>
                <slot name="meta">
                    <QuestSheetCreateChildMetaFields
                        v-if="editableMeta"
                        v-model:tool-ids="toolIds"
                        v-model:difficulty="difficulty"
                        v-model:quest-tier="questTier"
                        v-model:skill-grants="skillGrants"
                        :tools="tools"
                        :disabled="disabled"
                    />
                </slot>
            </template>

            <slot name="sections">
                <QuestSheetCreateSections v-model="sections" :disabled="disabled" />
            </slot>
        </QuestSheetLayout>
    </div>
</template>

<script setup lang="ts">
import { mentorPersonalAssignmentSectionConfig } from '@/constants/mentor-quest/questAdmin';
import { createEmptySkillGrants, type SkillKey } from '@/constants/shared/skills';
import { DEFAULT_QUEST_TIER, type QuestTier } from '@/constants/quest/questTier';
import type { MentorTool } from '@/types/mentor-quest/questAdmin';
import type { QuestDescriptionSections } from '@/utils/quest-sheet/questDescriptionSections';
import QuestSheetCreateChildMetaFields from '@/components/rpg/quest-sheet/QuestSheetCreateChildMetaFields.vue';
import QuestSheetCreateSections from '@/components/rpg/quest-sheet/QuestSheetCreateSections.vue';
import QuestSheetLayout from '@/components/rpg/quest-sheet/QuestSheetLayout.vue';
import QuestSheetPersonalTitleField from '@/components/rpg/quest-sheet/QuestSheetPersonalTitleField.vue';

withDefaults(
    defineProps<{
        questNo: number | null;
        sectionTitle?: string;
        statusLabel?: string;
        childQuestStyle?: boolean;
        editableMeta?: boolean;
        tools?: MentorTool[];
        disabled?: boolean;
        titleId?: string;
        titlePlaceholder?: string;
        titleReadonly?: boolean;
        titleRequired?: boolean;
    }>(),
    {
        sectionTitle: '',
        statusLabel: mentorPersonalAssignmentSectionConfig.childQuestTypeLabel,
        childQuestStyle: true,
        editableMeta: false,
        tools: () => [],
        disabled: false,
        titleId: undefined,
        titlePlaceholder: '',
        titleReadonly: false,
        titleRequired: false,
    },
);

const title = defineModel<string>('title', { default: '' });
const sections = defineModel<QuestDescriptionSections>('sections', { required: true });
const toolIds = defineModel<number[]>('toolIds', { default: () => [] });
const difficulty = defineModel<number | null>('difficulty', { default: null });
const questTier = defineModel<QuestTier>('questTier', { default: DEFAULT_QUEST_TIER });
const skillGrants = defineModel<SkillKey[]>('skillGrants', {
    default: () => createEmptySkillGrants(),
});
</script>
