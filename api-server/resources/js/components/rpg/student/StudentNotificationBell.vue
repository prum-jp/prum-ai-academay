<template>
    <div ref="rootRef" class="student-notification-bell">
        <button
            type="button"
            class="student-notification-trigger"
            :aria-expanded="isOpen"
            :aria-label="studentNotificationsConfig.ariaLabel"
            @click="toggleMenu"
        >
            <i class="fa-solid fa-bell" aria-hidden="true"></i>
            <span v-if="total > 0" class="student-notification-badge">{{ total }}</span>
        </button>

        <Teleport to="body">
            <div
                v-if="isOpen"
                ref="panelRef"
                class="student-notification-panel"
                :style="panelStyle"
            >
                <header class="student-notification-panel-header">
                    {{ studentNotificationsConfig.title }}
                </header>

                <p v-if="isLoading" class="student-notification-panel-empty">
                    {{ studentNotificationsConfig.loading }}
                </p>
                <p v-else-if="error" class="student-notification-panel-error">{{ error }}</p>
                <p v-else-if="items.length === 0" class="student-notification-panel-empty">
                    {{ studentNotificationsConfig.empty }}
                </p>
                <ul v-else class="student-notification-list">
                    <li v-for="item in items" :key="item.id">
                        <button
                            type="button"
                            class="student-notification-item"
                            @click="onSelect(item)"
                        >
                            <span class="student-notification-item-message">{{ item.message }}</span>
                            <time class="student-notification-item-time">
                                {{ formatDateTime(item.createdAt) }}
                            </time>
                        </button>
                    </li>
                </ul>
            </div>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { CSSProperties } from 'vue';
import { useRouter } from 'vue-router';
import { studentNotificationsConfig } from '@/constants/student/studentNotifications';
import { useStudentNotifications } from '@/composables/student/useStudentNotifications';
import type { StudentNotificationItem } from '@/types/student/studentNotification';
import { formatDateTime } from '@/utils/shared/formatDateTime';

const router = useRouter();
const { items, total, isLoading, error, refresh, markAsRead } = useStudentNotifications();

const rootRef = ref<HTMLElement | null>(null);
const panelRef = ref<HTMLElement | null>(null);
const isOpen = ref(false);
const panelStyle = ref<CSSProperties>({});

const updatePanelPosition = (): void => {
    const trigger = rootRef.value;
    if (!trigger) {
        return;
    }

    const rect = trigger.getBoundingClientRect();
    panelStyle.value = {
        position: 'fixed',
        top: `${rect.bottom + 8}px`,
        right: `${Math.max(12, window.innerWidth - rect.right)}px`,
        zIndex: 1300,
    };
};

const openMenu = async (): Promise<void> => {
    isOpen.value = true;
    await refresh();
    await nextTick();
    updatePanelPosition();
};

const closeMenu = (): void => {
    isOpen.value = false;
};

const toggleMenu = (): void => {
    if (isOpen.value) {
        closeMenu();
        return;
    }

    void openMenu();
};

const resolveRoute = (item: StudentNotificationItem): { name: string; params?: Record<string, string | number> } => {
    if (item.questId !== null) {
        return {
            name: 'student-quest-detail',
            params: { questId: item.questId },
        };
    }

    return { name: 'student-quests' };
};

const onSelect = async (item: StudentNotificationItem): Promise<void> => {
    const routeTarget = resolveRoute(item);
    const marked = await markAsRead(item.id);
    if (!marked) {
        return;
    }

    closeMenu();
    await router.push(routeTarget);
};

const onDocumentClick = (event: MouseEvent): void => {
    const target = event.target as Node;

    if (rootRef.value?.contains(target) || panelRef.value?.contains(target)) {
        return;
    }

    closeMenu();
};

const onDocumentKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
        closeMenu();
    }
};

const onViewportChange = (): void => {
    if (isOpen.value) {
        updatePanelPosition();
    }
};

onMounted(() => {
    void refresh();
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onDocumentKeydown);
    window.addEventListener('resize', onViewportChange);
    window.addEventListener('scroll', onViewportChange, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onDocumentKeydown);
    window.removeEventListener('resize', onViewportChange);
    window.removeEventListener('scroll', onViewportChange, true);
});

watch(
    () => router.currentRoute.value.fullPath,
    () => {
        void refresh();
    },
);
</script>
