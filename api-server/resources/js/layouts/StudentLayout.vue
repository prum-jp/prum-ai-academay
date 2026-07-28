<template>
    <div class="student-stage">
        <PageArrow direction="left" label="前のページ" @click="goToAdjacentPage(-1)" />

        <div class="student-stage-main">
            <PageIndicator
                :label="currentPage.label"
                :total="studentPages.length"
                :current-index="currentIndex"
                :titles="pageTitles"
            />

            <GameWindow
                :title="currentPage.windowTitle"
                :subtitle="currentPage.subtitle"
                :icon="currentPage.icon"
                :single-column="currentPage.singleColumn"
                :plain-content="currentPage.plainContent"
            >
                <template #toolbar>
                    <AudioToggle :enabled="audioEnabled" @toggle="toggleAudio" />
                </template>
                <RouterView />
            </GameWindow>
        </div>

        <PageArrow direction="right" label="次のページ" @click="goToAdjacentPage(1)" />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterView, useRoute, useRouter } from 'vue-router';
import { studentPages } from '@/constants/studentPages';
import { useGameAudio } from '@/composables/useGameAudio';
import PageArrow from '@/components/rpg/PageArrow.vue';
import PageIndicator from '@/components/rpg/PageIndicator.vue';
import GameWindow from '@/components/rpg/GameWindow.vue';
import AudioToggle from '@/components/rpg/AudioToggle.vue';

const route = useRoute();
const router = useRouter();
const { audioEnabled, playSound, toggleAudio } = useGameAudio();

const currentIndex = computed(() => {
    const index = studentPages.findIndex((page) => page.name === route.name);
    return index >= 0 ? index : 0;
});

const currentPage = computed(() => studentPages[currentIndex.value] ?? studentPages[0]);
const pageTitles = studentPages.map((page) => page.label);

const goToAdjacentPage = (direction: -1 | 1): void => {
    const total = studentPages.length;
    const nextIndex = (currentIndex.value + direction + total) % total;
    const nextPage = studentPages[nextIndex];

    if (!nextPage) {
        return;
    }

    playSound(direction > 0 ? 'click' : 'down');
    void router.push(nextPage.path);
};
</script>
