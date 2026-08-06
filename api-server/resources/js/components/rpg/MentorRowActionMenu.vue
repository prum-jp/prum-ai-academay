<template>
    <div ref="menuRef" class="mentor-quest-unit-menu-wrap">
        <button
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

        <ul v-if="isMenuOpen" class="mentor-quest-unit-menu">
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
        </ul>
    </div>
</template>

<script setup lang="ts">
import { onUnmounted, ref, watch } from 'vue';
import { RouterLink, type RouteLocationRaw } from 'vue-router';
import { mentorQuestMasterMenuConfig } from '@/constants/questMaster';

defineProps<{
    detailTo: RouteLocationRaw;
    editTo: RouteLocationRaw;
    disabled?: boolean;
}>();

const menuRef = ref<HTMLElement | null>(null);
const isMenuOpen = ref(false);

const closeMenu = (): void => {
    isMenuOpen.value = false;
};

const toggleMenu = (): void => {
    isMenuOpen.value = !isMenuOpen.value;
};

const onDocumentClick = (event: MouseEvent): void => {
    if (!menuRef.value?.contains(event.target as Node)) {
        closeMenu();
    }
};

watch(isMenuOpen, (open) => {
    if (open) {
        document.addEventListener('click', onDocumentClick);
    } else {
        document.removeEventListener('click', onDocumentClick);
    }
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
});
</script>

<style scoped>
a.mentor-quest-unit-menu-item {
    display: block;
    text-decoration: none;
}
</style>
