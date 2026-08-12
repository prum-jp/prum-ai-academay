<template>
    <aside class="quest-sheet-meta quest-sheet-create-meta">
        <table class="quest-sheet-meta-table">
            <tbody>
                <tr>
                    <th>{{ questSheetConfig.metaLabels.targetLevel }}</th>
                    <td>
                        <input
                            id="quest-create-unlock-level"
                            v-model.number="form.unlockLevel"
                            class="quest-sheet-create-meta-input"
                            type="number"
                            min="1"
                            max="99"
                            :placeholder="mentorQuestFormPlaceholders.unlockLevel"
                            :disabled="disabled"
                        />
                    </td>
                </tr>
                <tr>
                    <th>{{ questSheetConfig.metaLabels.acquiredSkill }}</th>
                    <td>
                        <input
                            id="quest-create-badge-label"
                            v-model="form.badgeLabel"
                            class="quest-sheet-create-meta-input"
                            type="text"
                            maxlength="255"
                            :placeholder="mentorQuestFormPlaceholders.badgeLabel"
                            :disabled="disabled"
                        />
                    </td>
                </tr>
                <tr>
                    <th>{{ questSheetConfig.metaLabels.difficulty }}</th>
                    <td>
                        <input
                            id="quest-create-difficulty"
                            v-model.number="form.difficulty"
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
                    <th>{{ mentorQuestFormLabels.rewardText }}</th>
                    <td>
                        <input
                            id="quest-create-reward-text"
                            v-model="form.rewardText"
                            class="quest-sheet-create-meta-input"
                            type="text"
                            maxlength="500"
                            :placeholder="mentorQuestFormPlaceholders.rewardText"
                            :disabled="disabled"
                        />
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="showSkillGrants" class="quest-sheet-create-meta-skills">
            <p class="quest-sheet-create-meta-skills-label">
                {{ mentorQuestFormLabels.rewardsTitle }}
            </p>
            <QuestSkillGrantFields
                v-model="skillGrants"
                variant="metaTable"
                :disabled="disabled"
            />
        </div>

        <label class="quest-sheet-create-required">
            <input
                v-model="form.isRequired"
                type="checkbox"
                :disabled="disabled"
            />
            {{ mentorQuestFormLabels.isRequired }}
        </label>
    </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { mentorChildQuestFormLabels, mentorQuestFormLabels, mentorQuestFormPlaceholders } from '@/constants/mentor-quest/questAdmin';
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import type { SkillKey } from '@/constants/shared/skills';
import { formatExperiencePointsFromDifficulty } from '@/utils/quest/questDifficulty';
import MentorToolSelectField from '@/components/rpg/mentor-tools/MentorToolSelectField.vue';
import QuestSkillGrantFields from '@/components/rpg/quest-sheet/QuestSkillGrantFields.vue';
import type { MentorTool } from '@/types/mentor-quest/questAdmin';

interface QuestMetaFormLike {
    unlockLevel: number | null;
    badgeLabel: string;
    rewardText: string;
    difficulty: number | null;
    isRequired: boolean;
}

const skillGrants = defineModel<SkillKey[]>('skillGrants', { default: () => [] });
const toolIds = defineModel<number[]>('toolIds', { default: () => [] });

const props = withDefaults(
    defineProps<{
        form: QuestMetaFormLike;
        tools?: MentorTool[];
        disabled?: boolean;
        showSkillGrants?: boolean;
    }>(),
    {
        tools: () => [],
        disabled: false,
        showSkillGrants: false,
    },
);

const experiencePointsLabel = computed(() =>
    formatExperiencePointsFromDifficulty(props.form.difficulty),
);
</script>
