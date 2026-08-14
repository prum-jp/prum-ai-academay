<template>
    <div ref="wrapRef" class="mentor-quest-unit-menu-wrap">
        <button
            ref="triggerRef"
            type="button"
            class="mentor-quest-unit-menu-btn"
            :class="{ 'is-open': isMenuOpen }"
            :aria-label="mentorQuestMasterMenuConfig.menuLabel"
            :aria-expanded="isMenuOpen"
            :disabled="disabled"
            @click.stop="toggleMenu"
        >
            <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
        </button>

        <Teleport to="body">
            <ul
                v-if="isMenuOpen"
                ref="menuRef"
                class="mentor-quest-unit-menu mentor-quest-unit-menu--master"
                :style="menuStyle"
            >
                <li>
                    <RouterLink
                        :to="detailTo"
                        class="mentor-quest-unit-menu-item"
                        @click="closeMenu"
                    >
                        {{ mentorQuestMasterMenuConfig.detailLabel }}
                    </RouterLink>
                </li>
                <li>
                    <RouterLink
                        :to="editTo"
                        class="mentor-quest-unit-menu-item"
                        @click="closeMenu"
                    >
                        {{ mentorQuestMasterMenuConfig.editLabel }}
                    </RouterLink>
                </li>
                <li>
                    <button
                        type="button"
                        class="mentor-quest-unit-menu-item is-danger"
                        @click="onDelete"
                    >
                        {{ mentorQuestMasterMenuConfig.deleteLabel }}
                    </button>
                </li>
            </ul>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { CSSProperties } from 'vue';
import { RouterLink, type RouteLocationRaw } from 'vue-router';
import { mentorQuestMasterMenuConfig } from '@/constants/mentor-master/questMaster';
import { useMentorRowActionMenuState } from '@/composables/mentor-master/useMentorRowActionMenuState';

defineProps<{
    detailTo: RouteLocationRaw;
    editTo: RouteLocationRaw;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    delete: [];
}>();

const wrapRef = ref<HTMLElement | null>(null);
const triggerRef = ref<HTMLButtonElement | null>(null);
const menuRef = ref<HTMLElement | null>(null);
const menuStyle = ref<CSSProperties>({});
const { isMenuOpen, openMenu: markMenuOpen, closeMenu } = useMentorRowActionMenuState();

const updateMenuPosition = (): void => {
    const trigger = triggerRef.value;
    if (!trigger) {
        return;
    }

    const rect = trigger.getBoundingClientRect();
    const menuHeight = menuRef.value?.offsetHeight ?? 132;
    const gap = 6;
    const spaceBelow = window.innerHeight - rect.bottom;
    const openUpward = spaceBelow < menuHeight + gap && rect.top > menuHeight + gap;

    menuStyle.value = {
        position: 'fixed',
        top: openUpward ? `${rect.top - menuHeight - gap}px` : `${rect.bottom + gap}px`,
        left: `${rect.right}px`,
        transform: 'translateX(-100%)',
        zIndex: 1200,
    };
};

const openMenu = async (): Promise<void> => {
    markMenuOpen();
    await nextTick();
    updateMenuPosition();
    await nextTick();
    updateMenuPosition();
};

const toggleMenu = (): void => {
    if (isMenuOpen.value) {
        closeMenu();
        return;
    }

    void openMenu();
};

const onDelete = (): void => {
    closeMenu();
    emit('delete');
};

const onDocumentClick = (event: MouseEvent): void => {
    const target = event.target as Node;

    if (wrapRef.value?.contains(target) || menuRef.value?.contains(target)) {
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
    if (isMenuOpen.value) {
        updateMenuPosition();
    }
};

watch(isMenuOpen, (open) => {
    if (!open) {
        menuStyle.value = {};
    }
});

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onDocumentKeydown);
    window.addEventListener('resize', onViewportChange);
    window.addEventListener('scroll', onViewportChange, true);
});

onBeforeUnmount(() => {
    closeMenu();
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onDocumentKeydown);
    window.removeEventListener('resize', onViewportChange);
    window.removeEventListener('scroll', onViewportChange, true);
});
</script>

<style scoped>
a.mentor-quest-unit-menu-item {
    display: block;
    text-decoration: none;
}
</style>
