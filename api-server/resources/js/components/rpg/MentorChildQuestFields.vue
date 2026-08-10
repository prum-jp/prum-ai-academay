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

            <div class="input-group">
                <label :for="`child-title-${index}`">{{ mentorChildQuestFormLabels.title }}</label>
                <input
                    :id="`child-title-${index}`"
                    v-model="quest.title"
                    type="text"
                    maxlength="255"
                    :disabled="disabled"
                />
            </div>

            <div class="input-group">
                <label :for="`child-description-${index}`">{{ mentorChildQuestFormLabels.description }}</label>
                <textarea
                    :id="`child-description-${index}`"
                    v-model="quest.description"
                    rows="2"
                    maxlength="2000"
                    :disabled="disabled"
                />
            </div>

            <div class="input-group">
                <label :for="`child-clear-${index}`">{{ mentorChildQuestFormLabels.clearCondition }}</label>
                <textarea
                    :id="`child-clear-${index}`"
                    v-model="quest.clearCondition"
                    rows="2"
                    maxlength="2000"
                    :disabled="disabled"
                />
            </div>

            <div class="input-group">
                <label>{{ mentorChildQuestFormLabels.tool }}</label>
                <MentorToolSelectField
                    v-model="quest.toolIds"
                    :tools="tools"
                    :disabled="disabled"
                />
            </div>

            <div class="input-group">
                <label :for="`child-difficulty-${index}`">{{ questSheetConfig.metaLabels.difficulty }}</label>
                <input
                    :id="`child-difficulty-${index}`"
                    v-model.number="quest.difficulty"
                    type="number"
                    min="1"
                    max="5"
                    :placeholder="mentorChildQuestFormLabels.difficultyPlaceholder"
                    :disabled="disabled"
                />
            </div>

            <div class="input-group">
                <label :for="`child-tier-${index}`">{{ mentorChildQuestFormLabels.questTier }}</label>
                <select
                    :id="`child-tier-${index}`"
                    v-model="quest.questTier"
                    :disabled="disabled"
                >
                    <option
                        v-for="option in QUEST_TIER_OPTIONS"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}（{{ option.requirement }}）
                    </option>
                </select>
            </div>

            <QuestSkillGrantFields
                v-model="quest.skillGrants"
                :disabled="disabled"
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
import type { MentorChildQuestInput, MentorTool } from '@/types/questAdmin';
import { mentorChildQuestFormLabels } from '@/constants/questAdmin';
import { QUEST_TIER_OPTIONS } from '@/constants/questTier';
import { questSheetConfig } from '@/constants/questSheet';
import QuestSkillGrantFields from '@/components/rpg/QuestSkillGrantFields.vue';
import MentorToolSelectField from '@/components/rpg/MentorToolSelectField.vue';

defineProps<{
    quests: MentorChildQuestInput[];
    tools: MentorTool[];
    disabled?: boolean;
}>();

defineEmits<{
    add: [];
    remove: [index: number];
}>();
</script>
