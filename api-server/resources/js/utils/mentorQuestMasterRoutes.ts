import type { RouteLocationRaw } from 'vue-router';

export const mentorQuestMasterQuestDetailRoute = (questId: number): RouteLocationRaw => ({
    name: 'mentor-quest-detail',
    params: { questId },
});

export const mentorQuestMasterQuestEditRoute = (questId: number): RouteLocationRaw => ({
    name: 'mentor-quest-edit',
    params: { questId },
});

export const mentorQuestMasterUnitDetailRoute = (unitId: number): RouteLocationRaw => ({
    name: 'mentor-quest-unit-detail',
    params: { unitId },
});

export const mentorQuestMasterUnitEditRoute = (unitId: number): RouteLocationRaw => ({
    name: 'mentor-quest-unit-edit',
    params: { unitId },
});
