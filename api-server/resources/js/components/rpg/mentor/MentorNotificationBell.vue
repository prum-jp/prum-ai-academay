<template>
    <div ref="rootRef" class="student-notification-bell">
        <button
            type="button"
            class="student-notification-trigger"
            :aria-expanded="isOpen"
            :aria-label="mentorNotificationConfig.ariaLabel"
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
                    {{ mentorNotificationConfig.title }}
                </header>

                <p v-if="isLoading" class="student-notification-panel-empty">
                    {{ mentorNotificationConfig.loading }}
                </p>
                <p v-else-if="error" class="student-notification-panel-error">{{ error }}</p>
                <p v-else-if="items.length === 0" class="student-notification-panel-empty">
                    {{ mentorNotificationConfig.empty }}
                </p>
                <ul
                    v-else
                    class="student-notification-list"
                    :class="{ 'is-scrollable': items.length > 3 }"
                >
                    <li v-for="item in items" :key="item.id" class="student-notification-row">
                        <button
                            type="button"
                            class="student-notification-item"
                            :disabled="openingId === item.id"
                            @click="onSelect(item)"
                        >
                            <span class="student-notification-item-message">{{ item.message }}</span>
                            <time class="student-notification-item-time">
                                {{ formatDateTime(item.createdAt) }}
                            </time>
                        </button>
                        <button
                            type="button"
                            class="student-notification-delete"
                            :aria-label="mentorNotificationConfig.deleteLabel"
                            :disabled="deletingId === item.id || openingId === item.id"
                            @click="onDelete(item)"
                        >
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
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
import { selectMentorStudent } from '@/api/mentor/mentor';
import { mentorNotificationConfig } from '@/constants/mentor/mentorNotification';
import { useMentorNotifications } from '@/composables/mentor/useMentorNotifications';
import type { MentorNotificationItem } from '@/types/mentor/mentorNotification';
import { formatDateTime } from '@/utils/shared/formatDateTime';

const router = useRouter();
const { items, total, isLoading, error, refresh, removeNotification } = useMentorNotifications();

const rootRef = ref<HTMLElement | null>(null);
const panelRef = ref<HTMLElement | null>(null);
const isOpen = ref(false);
const deletingId = ref<number | null>(null);
const openingId = ref<number | null>(null);
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

const onSelect = async (item: MentorNotificationItem): Promise<void> => {
    if (item.studentId === null || item.questId === null || openingId.value !== null) {
        return;
    }

    openingId.value = item.id;

    try {
        await selectMentorStudent(item.studentId);
        closeMenu();
        await router.push({
            name: 'student-quest-detail',
            params: { questId: item.questId },
        });
    } catch {
        error.value = mentorNotificationConfig.openFailed;
    } finally {
        openingId.value = null;
    }
};

const onDelete = async (item: MentorNotificationItem): Promise<void> => {
    if (deletingId.value !== null) {
        return;
    }

    deletingId.value = item.id;
    await removeNotification(item.id);
    deletingId.value = null;
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
