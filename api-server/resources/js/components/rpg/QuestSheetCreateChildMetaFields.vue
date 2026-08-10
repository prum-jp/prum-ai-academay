<template>
    <aside class="quest-sheet-meta quest-sheet-create-meta">
        <table class="quest-sheet-meta-table">
            <tbody>
                <tr>
                    <th>{{ questSheetConfig.metaLabels.difficulty }}</th>
                    <td>
                        <input
                            id="child-quest-create-difficulty"
                            v-model.number="difficulty"
                            class="quest-sheet-create-meta-input"
                            type="number"
                            min="1"
                            max="5"
                            :placeholder="mentorChildQuestFormLabels.difficultyPlaceholder"
                            :disabled="disabled"
                        />
                    </td>
                </tr>
                <tr>
                    <th>{{ questSheetConfig.metaLabels.experiencePoints }}</th>
                    <td class="quest-sheet-create-meta-readonly">
                        {{ experiencePointsLabel }}
                    </td>
                </tr>
                <tr>
                    <th>{{ questSheetConfig.metaLabels.recommendedTool }}</th>
                    <td>
                        <MentorToolSelectField
                            v-model="toolIds"
                            :tools="tools"
                            :disabled="disabled"
                        />
                    </td>
                </tr>
                <tr>
                    <th>{{ questSheetConfig.metaLabels.questTier }}</th>
                    <td>
                        <select
                            id="child-quest-create-tier"
                            v-model="questTier"
                            class="quest-sheet-create-meta-input"
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
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="quest-sheet-create-meta-skills">
            <p class="quest-sheet-create-meta-skills-label">
                {{ mentorQuestFormLabels.rewardsTitle }}
            </p>
            <QuestSkillGrantFields
                v-model="skillGrants"
                variant="metaTable"
                :disabled="disabled"
            />
        </div>
    </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { mentorChildQuestFormLabels, mentorQuestFormLabels } from '@/constants/questAdmin';
import { QUEST_TIER_OPTIONS } from '@/constants/questTier';
import { questSheetConfig } from '@/constants/questSheet';
import type { MentorTool } from '@/types/questAdmin';
import type { SkillKey } from '@/constants/skills';
import type { QuestTier } from '@/constants/questTier';
import { formatExperiencePointsFromDifficulty } from '@/utils/questDifficulty';
import MentorToolSelectField from '@/components/rpg/MentorToolSelectField.vue';
import QuestSkillGrantFields from '@/components/rpg/QuestSkillGrantFields.vue';

const toolIds = defineModel<number[]>('toolIds', { required: true });
const difficulty = defineModel<number | null>('difficulty', { default: null });
const questTier = defineModel<QuestTier>('questTier', { default: 'low' });
const skillGrants = defineModel<SkillKey[]>('skillGrants', { default: () => [] });

withDefaults(
    defineProps<{
        tools: MentorTool[];
        disabled?: boolean;
    }>(),
    {
        disabled: false,
    },
);

const experiencePointsLabel = computed(() =>
    formatExperiencePointsFromDifficulty(difficulty.value),
);
</script>
