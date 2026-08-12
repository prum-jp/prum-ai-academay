<!-- TODO: 後に機能追加 — 実績バッジコレクション（スキルブックページから利用） -->
<template>
    <RpgCard :title="badgeCollectionConfig.cardTitle" :icon="badgeCollectionConfig.cardIcon">
        <BadgeSummary
            :earned-count="meta?.earnedCount ?? 0"
            :total-count="meta?.totalCount ?? 0"
        />

        <AsyncState
            :is-loading="isLoading"
            :error="error"
            :is-empty="badges.length === 0"
            :loading-message="badgeCollectionConfig.loadingMessage"
            :empty-message="badgeCollectionConfig.emptyMessage"
        >
            <BadgeGrid :badges="badges" />
        </AsyncState>

        <div class="badge-notes">
            <p v-for="(note, index) in badgeCollectionConfig.notes" :key="index">
                {{ note }}
            </p>
        </div>
    </RpgCard>
</template>

<script setup lang="ts">
import { useBadgeCollection } from '@/composables/student/useBadgeCollection';
import RpgCard from '@/components/rpg/shared/RpgCard.vue';
import AsyncState from '@/components/rpg/shared/AsyncState.vue';
import BadgeSummary from '@/components/rpg/badge/BadgeSummary.vue';
import BadgeGrid from '@/components/rpg/badge/BadgeGrid.vue';

const {
    badgeCollectionConfig,
    badges,
    meta,
    isLoading,
    error,
} = useBadgeCollection();
</script>
