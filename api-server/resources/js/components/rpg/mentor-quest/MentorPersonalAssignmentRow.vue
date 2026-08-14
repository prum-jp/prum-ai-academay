<template>
    <article class="mentor-personal-assignment-row">
        <div class="mentor-personal-assignment-main">
            <div class="mentor-student-avatar">
                <img v-if="student.avatarUrl" :src="student.avatarUrl" :alt="student.name" />
                <span v-else>{{ avatarConfig.placeholderLabel }}</span>
            </div>

            <div class="mentor-student-body">
                <div class="mentor-student-title-row">
                    <h3>{{ student.name }}</h3>
                </div>
                <p class="mentor-student-email">{{ student.email }}</p>
                <p class="mentor-student-meta">
                    <span>{{ student.levelTitle }}</span>
                    <!-- TODO: 後に機能追加 — 獲得バッジ数表示 -->
                    <!-- <span>バッジ {{ student.earnedBadgeCount }}</span> -->
                </p>
            </div>
        </div>

        <div ref="menuRef" class="mentor-quest-unit-menu-wrap">
            <button
                type="button"
                class="mentor-quest-unit-menu-btn"
                :class="{ 'is-open': isMenuOpen }"
                :aria-label="mentorPersonalAssignmentSectionConfig.menuLabel"
                :aria-expanded="isMenuOpen"
                :disabled="disabled"
                @click.stop="toggleMenu"
            >
                <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
            </button>

            <ul v-if="isMenuOpen" class="mentor-quest-unit-menu">
                <li>
                    <button
                        type="button"
                        class="mentor-quest-unit-menu-item"
                        :disabled="disabled"
                        @click="onAssignClick"
                    >
                        {{ mentorPersonalAssignmentSectionConfig.menuAssignLabel }}
                    </button>
                </li>
                <li>
                    <button
                        type="button"
                        class="mentor-quest-unit-menu-item"
                        :disabled="disabled"
                        @click="onHomeClick"
                    >
                        {{ mentorPersonalAssignmentSectionConfig.menuHomeLabel }}
                    </button>
                </li>
                <li>
                    <button
                        type="button"
                        class="mentor-quest-unit-menu-item is-danger"
                        :disabled="disabled"
                        @click="onDeleteClick"
                    >
                        {{ mentorPersonalAssignmentSectionConfig.menuDeleteLabel }}
                    </button>
                </li>
            </ul>
        </div>
    </article>
</template>

<script setup lang="ts">
import { onUnmounted, ref, watch } from 'vue';
import type { MentorStudent } from '@/types/mentor/mentor';
import { useExclusiveRowMenuState } from '@/composables/shared/useExclusiveRowMenuState';
import { avatarConfig } from '@/constants/profile/avatar';
import { mentorPersonalAssignmentSectionConfig } from '@/constants/mentor-quest/questAdmin';

const props = defineProps<{
    student: MentorStudent;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'open-assign': [student: MentorStudent];
    'open-home': [student: MentorStudent];
    delete: [student: MentorStudent];
}>();

const menuRef = ref<HTMLElement | null>(null);
const { isMenuOpen, openMenu: markMenuOpen, closeMenu } = useExclusiveRowMenuState();

const toggleMenu = (): void => {
    if (props.disabled) {
        return;
    }

    if (isMenuOpen.value) {
        closeMenu();
        return;
    }

    markMenuOpen();
};

const onDocumentClick = (event: MouseEvent): void => {
    if (!menuRef.value?.contains(event.target as Node)) {
        closeMenu();
    }
};

watch(isMenuOpen, (open) => {
    if (open) {
        document.addEventListener('click', onDocumentClick);
        document.addEventListener('keydown', onDocumentKeydown);
    } else {
        document.removeEventListener('click', onDocumentClick);
        document.removeEventListener('keydown', onDocumentKeydown);
    }
});

const onDocumentKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
        closeMenu();
    }
};

onUnmounted(() => {
    closeMenu();
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onDocumentKeydown);
});

const onAssignClick = (): void => {
    closeMenu();
    emit('open-assign', props.student);
};

const onHomeClick = (): void => {
    closeMenu();
    emit('open-home', props.student);
};

const onDeleteClick = (): void => {
    closeMenu();
    emit('delete', props.student);
};
</script>
