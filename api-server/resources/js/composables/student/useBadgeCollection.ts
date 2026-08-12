// TODO: 後に機能追加 — 実績バッジ一覧（スキルブックページから利用）
import { onMounted, ref } from 'vue';
import { fetchBadges } from '@/api/badge/badges';
import { badgeCollectionConfig } from '@/constants/badge/badges';
import type { BadgeItem, BadgeListMeta } from '@/types/badge/badge';

export function useBadgeCollection() {
    const badges = ref<BadgeItem[]>([]);
    const meta = ref<BadgeListMeta | null>(null);
    const isLoading = ref(true);
    const error = ref('');

    const loadBadges = async (): Promise<void> => {
        isLoading.value = true;
        error.value = '';

        try {
            const response = await fetchBadges();
            badges.value = response.data;
            meta.value = response.meta;
        } catch {
            error.value = badgeCollectionConfig.errorMessage;
            badges.value = [];
            meta.value = null;
        } finally {
            isLoading.value = false;
        }
    };

    onMounted(() => {
        void loadBadges();
    });

    return {
        badgeCollectionConfig,
        badges,
        meta,
        isLoading,
        error,
        loadBadges,
    };
}
