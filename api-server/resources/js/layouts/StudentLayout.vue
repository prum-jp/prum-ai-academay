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
                :single-column="layoutSingleColumn"
                :plain-content="currentPage.plainContent"
            >
                <RouterView />
            </GameWindow>
        </div>

        <PageArrow direction="right" label="次のページ" @click="goToAdjacentPage(1)" />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterView, useRoute, useRouter } from 'vue-router';
import { studentPages } from '@/constants/student/studentPages';
import PageArrow from '@/components/rpg/shared/PageArrow.vue';
import PageIndicator from '@/components/rpg/shared/PageIndicator.vue';
import GameWindow from '@/components/rpg/shared/GameWindow.vue';

const route = useRoute();
const router = useRouter();

const currentIndex = computed(() => {
    const pageName =
        typeof route.meta.studentPage === 'string' ? route.meta.studentPage : route.name;
    const index = studentPages.findIndex((page) => page.name === pageName);
    return index >= 0 ? index : 0;
});

const currentPage = computed(() => studentPages[currentIndex.value] ?? studentPages[0]);
const pageTitles = studentPages.map((page) => page.label);
const layoutSingleColumn = computed((): boolean => {
    if (typeof route.meta.singleColumn === 'boolean') {
        return route.meta.singleColumn;
    }

    return currentPage.value.singleColumn;
});

const goToAdjacentPage = (direction: -1 | 1): void => {
    const total = studentPages.length;
    const nextIndex = (currentIndex.value + direction + total) % total;
    const nextPage = studentPages[nextIndex];

    if (!nextPage) {
        return;
    }

    void router.push(nextPage.path);
};
</script>
