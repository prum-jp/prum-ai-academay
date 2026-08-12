<template>
    <section class="mentor-child-quest-section">
        <h4>{{ mentorChildQuestFormLabels.sectionTitle }}</h4>

        <p v-if="quests.length === 0" class="mentor-child-quest-empty">
            {{ mentorChildQuestFormLabels.empty }}
        </p>

        <div
            v-for="(quest, index) in quests"
            :key="quest.id ?? `new-${index}`"
            class="mentor-child-quest-card"
        >
            <div class="mentor-child-quest-card-head">
                <span class="mentor-child-quest-index">{{ index + 1 }}</span>
                <div class="mentor-child-quest-head-actions">
                    <button
                        type="button"
                        class="mentor-reward-remove"
                        :disabled="disabled"
                        @click="$emit('remove', index)"
                    >
                        {{ mentorChildQuestFormLabels.remove }}
                    </button>
                </div>
            </div>

            <QuestSheetPersonalQuestForm
                v-model:title="quest.title"
                v-model:sections="sectionForms[index]"
                v-model:tool-ids="quest.toolIds"
                v-model:difficulty="quest.difficulty"
                v-model:quest-tier="quest.questTier"
                v-model:skill-grants="quest.skillGrants"
                :quest-no="quest.sortOrder"
                :tools="tools"
                :disabled="disabled"
                editable-meta
                :title-id="`child-title-${index}`"
                :title-placeholder="mentorQuestFormPlaceholders.childQuestTitle"
                title-required
            />
        </div>

        <button
            type="button"
            class="mentor-reward-add"
            :disabled="disabled"
            @click="$emit('add')"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            {{ mentorChildQuestFormLabels.add }}
        </button>
    </section>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';
import {
    mentorChildQuestFormLabels,
    mentorQuestFormPlaceholders,
} from '@/constants/mentor-quest/questAdmin';
import type { MentorChildQuestInput, MentorTool } from '@/types/mentor-quest/questAdmin';
import QuestSheetPersonalQuestForm from '@/components/rpg/quest-sheet/QuestSheetPersonalQuestForm.vue';
import {
    createEmptyQuestDescriptionSections,
    parseQuestDescriptionSections,
    serializeQuestDescriptionSections,
    type QuestDescriptionSections,
} from '@/utils/quest-sheet/questDescriptionSections';

const props = defineProps<{
    quests: MentorChildQuestInput[];
    tools: MentorTool[];
    disabled?: boolean;
}>();

defineEmits<{
    add: [];
    remove: [index: number];
}>();

const sectionForms = reactive<QuestDescriptionSections[]>([]);

const syncSectionFormsLength = (): void => {
    while (sectionForms.length < props.quests.length) {
        const index = sectionForms.length;
        const sections = reactive(createEmptyQuestDescriptionSections());
        Object.assign(
            sections,
            parseQuestDescriptionSections(
                props.quests[index].description,
                props.quests[index].clearCondition,
            ),
        );
        sectionForms.push(sections);
    }

    while (sectionForms.length > props.quests.length) {
        sectionForms.pop();
    }
};

watch(() => props.quests.length, syncSectionFormsLength, { immediate: true });

watch(
    sectionForms,
    () => {
        props.quests.forEach((quest, index) => {
            const serialized = serializeQuestDescriptionSections(sectionForms[index]);
            quest.description = serialized.description;
            quest.clearCondition = serialized.clearCondition;
        });
    },
    { deep: true },
);
</script>
