import { computed, ref, type ComputedRef } from 'vue';

const activeMenuId = ref<string | null>(null);
let menuIdCounter = 0;

export function useMentorRowActionMenuState(): {
    isMenuOpen: ComputedRef<boolean>;
    openMenu: () => void;
    closeMenu: () => void;
} {
    const menuId = `mentor-row-menu-${++menuIdCounter}`;

    const isMenuOpen = computed(() => activeMenuId.value === menuId);

    const closeMenu = (): void => {
        if (activeMenuId.value === menuId) {
            activeMenuId.value = null;
        }
    };

    const openMenu = (): void => {
        activeMenuId.value = menuId;
    };

    return {
        isMenuOpen,
        openMenu,
        closeMenu,
    };
}
