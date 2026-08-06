<template>
    <div class="quest-notebook">
        <QuestBoardSection
            v-if="personalDefinition"
            :title="personalDefinition.title"
            :icon="personalDefinition.icon"
            :meta="personalUnits.meta"
            :is-empty="personalUnits.units.length === 0"
            :empty-message="personalUnitsEmptyMessage()"
            :is-loading="personalUnits.isLoading"
            :error="personalUnits.error"
            @page-change="loadPersonalUnits"
        >
            <template #filters>
                <QuestUnitProgressFilter
                    :model-value="personalUnits.progressFilter"
                    @update:model-value="setPersonalProgressFilter"
                />
            </template>

            <QuestUnitCard
                v-for="unit in personalUnits.units"
                :key="unit.id"
                :unit="unit"
                @open="() => openUnit(unit)"
            />
        </QuestBoardSection>

        <QuestBoardSection
            v-for="definition in questSectionDefinitions"
            :key="definition.type"
            :title="definition.title"
            :icon="definition.icon"
            :meta="sections[definition.type].meta"
            :is-empty="sections[definition.type].quests.length === 0"
            :empty-message="questMessages.emptyQuests"
            :is-loading="sections[definition.type].isLoading"
            :error="sections[definition.type].error"
            @page-change="(page) => loadSection(definition.type, page)"
        >
            <QuestItemCard
                v-for="quest in sections[definition.type].quests"
                :key="quest.id"
                :quest="quest"
                @open="() => openDetail(quest)"
            />
        </QuestBoardSection>

        <QuestUnitModal :unit="selectedUnit" @close="closeUnit" />

        <QuestDetailModal :quest="selectedQuest" @close="closeDetail" />
    </div>
</template>

<script setup lang="ts">
import { questMessages } from '@/constants/quests';
import { useQuestBoard } from '@/composables/useQuestBoard';
import QuestBoardSection from '@/components/rpg/QuestBoardSection.vue';
import QuestDetailModal from '@/components/rpg/QuestDetailModal.vue';
import QuestItemCard from '@/components/rpg/QuestItemCard.vue';
import QuestUnitCard from '@/components/rpg/QuestUnitCard.vue';
import QuestUnitModal from '@/components/rpg/QuestUnitModal.vue';
import QuestUnitProgressFilter from '@/components/rpg/QuestUnitProgressFilter.vue';

const {
    questSectionDefinitions,
    personalDefinition,
    personalUnits,
    sections,
    selectedQuest,
    selectedUnit,
    loadPersonalUnits,
    setPersonalProgressFilter,
    personalUnitsEmptyMessage,
    loadSection,
    openDetail,
    openUnit,
    closeDetail,
    closeUnit,
} = useQuestBoard();
</script>
