<template>
    <MentorPanel :config="pageConfig">
        <QuestSheetBackNav
            :back-to="backTo"
            :back-label="backLabel"
        >
            <template v-if="$slots['back-nav-secondary']" #secondary>
                <slot name="back-nav-secondary" />
            </template>
        </QuestSheetBackNav>

        <PageLoadGate
            :is-loading="isLoading"
            :load-error="loadError"
            :loading-message="loadingMessage"
            @retry="$emit('retry')"
        >
            <template v-if="ready">
                <div v-if="!bare" :class="pageClass">
                    <slot name="toolbar" />
                    <slot />
                </div>
                <template v-else>
                    <slot name="toolbar" />
                    <slot />
                </template>
            </template>
        </PageLoadGate>
    </MentorPanel>
</template>

<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router';
import MentorPanel from '@/components/rpg/mentor/MentorPanel.vue';
import PageLoadGate from '@/components/rpg/shared/PageLoadGate.vue';
import QuestSheetBackNav from '@/components/rpg/quest-sheet/QuestSheetBackNav.vue';

withDefaults(
    defineProps<{
        pageConfig: {
            title: string;
            icon: string;
        };
        isLoading: boolean;
        loadError?: string;
        loadingMessage: string;
        backLabel: string;
        backTo?: RouteLocationRaw;
        pageClass?: string;
        ready?: boolean;
        bare?: boolean;
    }>(),
    {
        loadError: '',
        backTo: () => ({ name: 'mentor-quest-master' }),
        pageClass: 'quest-sheet-page mentor-quest-create-page',
        ready: true,
        bare: false,
    },
);

defineEmits<{
    retry: [];
}>();
</script>
