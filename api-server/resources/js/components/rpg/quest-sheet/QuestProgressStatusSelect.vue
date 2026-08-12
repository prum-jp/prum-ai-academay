<template>
    <div
        ref="rootRef"
        class="quest-progress-select"
        :class="{ 'is-open': isOpen, 'is-disabled': disabled || isLocked }"
    >
        <button
            ref="triggerRef"
            type="button"
            class="quest-progress-badge quest-progress-select-trigger is-md"
            :class="statusClass"
            :disabled="disabled || isLocked || !canChange"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
            @click="toggleMenu"
        >
            {{ currentLabel }}
        </button>

        <Teleport to="body">
            <ul
                v-if="isOpen && canChange"
                ref="menuRef"
                class="quest-progress-select-menu"
                :style="menuStyle"
                role="listbox"
            >
                <li v-for="option in options" :key="option.value" role="option">
                    <button
                        type="button"
                        class="quest-progress-select-option"
                        :class="[
                            getQuestProgressStatusClass(option.value),
                            {
                                'is-current': option.value === status,
                                'is-unavailable': !option.selectable,
                            },
                        ]"
                        :disabled="
                            isUpdating ||
                            !option.selectable ||
                            option.value === status
                        "
                        :aria-selected="option.value === status"
                        @click="selectStatus(option.value)"
                    >
                        {{ option.label }}
                    </button>
                </li>
            </ul>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { CSSProperties } from 'vue';
import type { QuestProgressStatus } from '@/constants/quest/questProgress';
import { questProgressStatusLabels } from '@/constants/quest/questProgress';
import {
    buildQuestProgressSelectOptions,
    getQuestProgressStatusClass,
    getSelectableQuestProgressStatuses,
} from '@/utils/quest/questProgressDisplay';

const props = withDefaults(
    defineProps<{
        status: QuestProgressStatus;
        isLocked?: boolean;
        disabled?: boolean;
        isUpdating?: boolean;
        role?: 'student' | 'mentor';
    }>(),
    {
        role: 'student',
    },
);

const emit = defineEmits<{
    update: [status: QuestProgressStatus];
}>();

const rootRef = ref<HTMLElement | null>(null);
const triggerRef = ref<HTMLButtonElement | null>(null);
const menuRef = ref<HTMLElement | null>(null);
const isOpen = ref(false);
const menuStyle = ref<CSSProperties>({});

const statusClass = computed(() => getQuestProgressStatusClass(props.status));
const currentLabel = computed(() => questProgressStatusLabels[props.status]);
const canChange = computed(
    () => getSelectableQuestProgressStatuses(props.status, props.role).length > 0,
);
const options = computed(() => buildQuestProgressSelectOptions(props.status, props.role));

const updateMenuPosition = (): void => {
    const trigger = triggerRef.value;
    if (!trigger) {
        return;
    }

    const rect = trigger.getBoundingClientRect();
    menuStyle.value = {
        position: 'fixed',
        top: `${rect.bottom + 6}px`,
        left: `${rect.left + rect.width / 2}px`,
        transform: 'translateX(-50%)',
        minWidth: `${Math.max(rect.width, 168)}px`,
        zIndex: 1200,
    };
};

const openMenu = async (): Promise<void> => {
    isOpen.value = true;
    await nextTick();
    updateMenuPosition();
};

const toggleMenu = (): void => {
    if (!canChange.value || props.disabled || props.isLocked || props.isUpdating) {
        return;
    }

    if (isOpen.value) {
        closeMenu();
        return;
    }

    void openMenu();
};

const closeMenu = (): void => {
    isOpen.value = false;
};

const selectStatus = (next: QuestProgressStatus): void => {
    closeMenu();
    emit('update', next);
};

const onDocumentClick = (event: MouseEvent): void => {
    const target = event.target as Node;

    if (rootRef.value?.contains(target) || menuRef.value?.contains(target)) {
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
        updateMenuPosition();
    }
};

watch(
    () => props.status,
    () => {
        closeMenu();
    },
);

onMounted(() => {
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
</script>
