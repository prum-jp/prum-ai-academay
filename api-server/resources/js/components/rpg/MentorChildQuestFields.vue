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
                    <MentorPublishToggle
                        v-model="quest.isPublished"
                        on-label="公開"
                        off-label="非公開"
                        :disabled="disabled"
                    />
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
                <label :for="`child-tool-${index}`">{{ mentorChildQuestFormLabels.tool }}</label>
                <select
                    :id="`child-tool-${index}`"
                    v-model="quest.toolId"
                    :disabled="disabled"
                >
                    <option :value="null">{{ mentorChildQuestFormLabels.toolNone }}</option>
                    <option
                        v-for="tool in tools"
                        :key="tool.id"
                        :value="tool.id"
                    >
                        {{ tool.name }}
                    </option>
                </select>
            </div>
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
import MentorPublishToggle from '@/components/rpg/MentorPublishToggle.vue';

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
