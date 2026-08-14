import { computed, ref, type ComputedRef } from 'vue';

const activeMenuId = ref<string | null>(null);
let menuIdCounter = 0;

/** 同一画面内で1つの行メニューだけ開くための共有状態 */
export function useExclusiveRowMenuState(): {
    isMenuOpen: ComputedRef<boolean>;
    openMenu: () => void;
    closeMenu: () => void;
} {
    const menuId = `exclusive-row-menu-${++menuIdCounter}`;

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
