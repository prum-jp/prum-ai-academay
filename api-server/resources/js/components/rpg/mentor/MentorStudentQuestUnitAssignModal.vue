<template>
    <RpgModal
        :open="open"
        :title="modalTitle"
        icon="fa-solid fa-seedling"
        wide
        @close="onClose"
    >
        <AsyncState
            :is-loading="isLoading"
            :error="error ?? undefined"
            :is-empty="quests.length === 0"
            :loading-message="mentorPersonalAssignmentSectionConfig.loadingQuests"
            :empty-message="mentorPersonalAssignmentSectionConfig.emptyQuests"
        >
            <div class="mentor-quest-unit-assign-summary">
                <p class="mentor-message">{{ mentorPersonalAssignmentSectionConfig.modalDescription }}</p>
                <p class="mentor-quest-unit-assign-count">
                    {{ assignedSummaryText }}
                </p>
            </div>

            <ul class="mentor-quest-unit-assign-list">
                <li
                    v-for="(quest, index) in quests"
                    :key="quest.questUnitId"
                    class="mentor-quest-unit-assign-item"
                    :class="{
                        'is-dragging': draggedIndex === index,
                        'is-drag-over': dragOverIndex === index && draggedIndex !== index,
                    }"
                    @dragover.prevent="onDragOver(index)"
                    @drop.prevent="onDrop(index)"
                    @dragend="onDragEnd"
                >
                    <div
                        class="mentor-quest-unit-assign-quest"
                        :class="{ 'is-expanded': isExpanded(quest.questUnitId) }"
                    >
                        <button
                            type="button"
                            class="mentor-quest-unit-assign-drag-handle"
                            :aria-label="mentorPersonalAssignmentSectionConfig.dragHandleLabel"
                            :disabled="disabled || isReordering"
                            draggable="true"
                            @mousedown="enableDragFromHandle"
                            @mouseup="disableDragFromHandle"
                            @mouseleave="disableDragFromHandle"
                            @dragstart="onDragStart(index, $event)"
                        >
                            <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                        </button>

                        <button
                            type="button"
                            class="mentor-quest-unit-assign-toggle"
                            @click="toggleExpand(quest.questUnitId)"
                        >
                            <span class="mentor-quest-unit-assign-icon" aria-hidden="true">
                                <i
                                    :class="
                                        isExpanded(quest.questUnitId)
                                            ? 'fa-solid fa-chevron-down'
                                            : 'fa-solid fa-chevron-right'
                                    "
                                ></i>
                            </span>

                            <span class="mentor-quest-unit-assign-body">
                                <span class="mentor-quest-unit-assign-type">
                                    {{ mentorPersonalAssignmentSectionConfig.unitTypeLabel }}
                                </span>
                                <span class="mentor-quest-unit-assign-quest-name">
                                    {{ quest.name }}
                                </span>
                            </span>
                        </button>

                        <button
                            type="button"
                            class="mentor-quest-unit-assign-status"
                            :class="statusBadgeClass(quest)"
                            :disabled="
                                disabled
                                || togglingQuestUnitId === quest.questUnitId
                                || !isClickable(quest)
                            "
                            @click="onUnitAssignClick(quest)"
                        >
                            {{ statusLabel(quest) }}
                        </button>
                    </div>

                    <div
                        v-if="isExpanded(quest.questUnitId)"
                        class="mentor-quest-unit-assign-children"
                    >
                        <p
                            v-if="quest.childQuests.length === 0"
                            class="mentor-quest-unit-assign-children-empty"
                        >
                            {{ mentorPersonalAssignmentSectionConfig.emptyChildQuests }}
                        </p>

                        <ul v-else class="mentor-quest-unit-assign-children-list">
                            <template
                                v-for="(childQuest, childIndex) in quest.childQuests"
                                :key="childQuest.id"
                            >
                                <li
                                    v-if="childIndex > 0"
                                    class="quest-import-unit-divider"
                                    role="separator"
                                    aria-hidden="true"
                                >
                                    <span class="quest-import-unit-divider-rule"></span>
                                </li>

                                <li class="mentor-quest-unit-assign-child">
                                    <div class="mentor-quest-unit-assign-child-body">
                                        <span class="mentor-quest-unit-assign-child-type">
                                            {{ mentorPersonalAssignmentSectionConfig.childQuestTypeLabel }}
                                        </span>
                                        <button
                                            type="button"
                                            class="mentor-quest-unit-assign-child-name-link"
                                            :disabled="disabled || isNavigating"
                                            @click="onQuestTitleClick(childQuest.id)"
                                        >
                                            {{ childQuest.title }}
                                        </button>
                                    </div>

                                    <button
                                        type="button"
                                        class="mentor-quest-unit-assign-status"
                                        :class="statusBadgeClass(childQuest)"
                                        :disabled="
                                            disabled
                                            || togglingQuestId === childQuest.id
                                            || !isClickable(childQuest)
                                        "
                                        @click="onChildAssignClick(childQuest)"
                                    >
                                        {{ statusLabel(childQuest) }}
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </li>
            </ul>
        </AsyncState>

        <p v-if="navigationError" class="login-error">{{ navigationError }}</p>

        <template #footer>
            <button type="button" class="rpg-btn is-secondary" :disabled="disabled" @click="onClose">
                閉じる
            </button>
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import {
    assignStudentQuest,
    assignStudentQuestUnit,
    fetchStudentQuestUnitAssignments,
    unassignStudentQuest,
    unassignStudentQuestUnit,
} from '@/api/mentor-quest/questUnitAssignment';
import { reorderMentorQuestUnits } from '@/api/mentor-quest/questAdmin';
import { selectMentorStudent } from '@/api/mentor/mentor';
import { mentorPersonalAssignmentSectionConfig } from '@/constants/mentor-quest/questAdmin';
import { mentorStudentMessages } from '@/constants/mentor/mentor';
import type {
    MentorStudentQuestAssignmentStatus,
    MentorStudentQuestUnitAssignmentItem,
    MentorStudentQuestUnitChildQuestItem,
} from '@/types/mentor-quest/curriculum';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';
import AsyncState from '@/components/rpg/shared/AsyncState.vue';
import RpgModal from '@/components/rpg/shared/RpgModal.vue';

const router = useRouter();

type AssignableItem = MentorStudentQuestAssignmentStatus & {
    viaCurriculum?: boolean;
};

const props = defineProps<{
    open: boolean;
    studentId: number | null;
    studentName: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    close: [];
    notify: [message: string];
}>();

const quests = ref<MentorStudentQuestUnitAssignmentItem[]>([]);
const expandedQuestUnitIds = ref<number[]>([]);
const isLoading = ref(false);
const error = ref<string | null>(null);
const togglingQuestUnitId = ref<number | null>(null);
const togglingQuestId = ref<number | null>(null);
const isNavigating = ref(false);
const navigationError = ref('');
const isReordering = ref(false);
const draggedIndex = ref<number | null>(null);
const dragOverIndex = ref<number | null>(null);
let dragFromHandle = false;

const modalTitle = computed((): string => {
    if (!props.studentName) {
        return mentorPersonalAssignmentSectionConfig.modalTitle;
    }

    return `${props.studentName} の${mentorPersonalAssignmentSectionConfig.modalTitle}`;
});

const assignedCount = computed((): number =>
    quests.value.filter((quest) => quest.assigned).length,
);

const assignedSummaryText = computed((): string =>
    mentorPersonalAssignmentSectionConfig.assignedSummaryLabel(
        assignedCount.value,
        quests.value.length,
    ),
);

const isExpanded = (questUnitId: number): boolean =>
    expandedQuestUnitIds.value.includes(questUnitId);

const toggleExpand = (questUnitId: number): void => {
    if (isExpanded(questUnitId)) {
        expandedQuestUnitIds.value = expandedQuestUnitIds.value.filter((id) => id !== questUnitId);

        return;
    }

    expandedQuestUnitIds.value = [...expandedQuestUnitIds.value, questUnitId];
};

const statusLabel = (item: AssignableItem): string => {
    if (!item.assigned) {
        return mentorPersonalAssignmentSectionConfig.unassignedStatusLabel;
    }

    if (item.viaCurriculum) {
        return mentorPersonalAssignmentSectionConfig.viaCurriculumStatusLabel;
    }

    return mentorPersonalAssignmentSectionConfig.assignedStatusLabel;
};

const statusBadgeClass = (item: AssignableItem): Record<string, boolean> => ({
    'is-unassigned': !item.assigned,
    'is-assigned': item.assigned && !item.viaCurriculum,
    'is-via-curriculum': item.assigned && !!item.viaCurriculum,
});

const loadQuests = async (): Promise<void> => {
    if (props.studentId === null) {
        return;
    }

    isLoading.value = true;
    error.value = null;

    try {
        const data = await fetchStudentQuestUnitAssignments(props.studentId);
        quests.value = data.quests;
    } catch {
        error.value = mentorPersonalAssignmentSectionConfig.assignFailed;
        quests.value = [];
    } finally {
        isLoading.value = false;
    }
};

const isClickable = (item: AssignableItem): boolean => {
    if (!item.assigned) {
        return true;
    }

    return item.canUnassign;
};

const onUnitAssignClick = async (quest: MentorStudentQuestUnitAssignmentItem): Promise<void> => {
    if (props.studentId === null) {
        return;
    }

    togglingQuestUnitId.value = quest.questUnitId;

    try {
        const data = quest.assigned && quest.canUnassign
            ? await unassignStudentQuestUnit(props.studentId, quest.questUnitId)
            : await assignStudentQuestUnit(props.studentId, quest.questUnitId);

        quests.value = data.quests;
        emit(
            'notify',
            quest.assigned && quest.canUnassign
                ? mentorPersonalAssignmentSectionConfig.unassignSuccess
                : mentorPersonalAssignmentSectionConfig.assignSuccess,
        );
    } catch {
        emit(
            'notify',
            quest.assigned && quest.canUnassign
                ? mentorPersonalAssignmentSectionConfig.unassignFailed
                : mentorPersonalAssignmentSectionConfig.assignFailed,
        );
    } finally {
        togglingQuestUnitId.value = null;
    }
};

const onChildAssignClick = async (childQuest: MentorStudentQuestUnitChildQuestItem): Promise<void> => {
    if (props.studentId === null) {
        return;
    }

    togglingQuestId.value = childQuest.id;

    try {
        const data = childQuest.assigned && childQuest.canUnassign
            ? await unassignStudentQuest(props.studentId, childQuest.id)
            : await assignStudentQuest(props.studentId, childQuest.id);

        quests.value = data.quests;
        emit(
            'notify',
            childQuest.assigned && childQuest.canUnassign
                ? mentorPersonalAssignmentSectionConfig.unassignSuccess
                : mentorPersonalAssignmentSectionConfig.assignSuccess,
        );
    } catch {
        emit(
            'notify',
            childQuest.assigned && childQuest.canUnassign
                ? mentorPersonalAssignmentSectionConfig.unassignFailed
                : mentorPersonalAssignmentSectionConfig.assignFailed,
        );
    } finally {
        togglingQuestId.value = null;
    }
};

const onClose = (): void => {
    emit('close');
};

const enableDragFromHandle = (): void => {
    dragFromHandle = true;
};

const disableDragFromHandle = (): void => {
    dragFromHandle = false;
};

const onDragStart = (index: number, event: DragEvent): void => {
    if (!dragFromHandle || props.disabled || isReordering.value) {
        event.preventDefault();
        return;
    }

    draggedIndex.value = index;
    dragOverIndex.value = index;
    event.dataTransfer?.setData('text/plain', String(index));
    event.dataTransfer!.effectAllowed = 'move';
};

const onDragOver = (index: number): void => {
    if (draggedIndex.value === null) {
        return;
    }

    dragOverIndex.value = index;
};

const reorderLocalQuests = (fromIndex: number, toIndex: number): void => {
    if (fromIndex === toIndex) {
        return;
    }

    const reordered = [...quests.value];
    const [moved] = reordered.splice(fromIndex, 1);
    reordered.splice(toIndex, 0, moved);
    quests.value = reordered;
};

const persistUnitOrder = async (previousQuests: MentorStudentQuestUnitAssignmentItem[]): Promise<void> => {
    isReordering.value = true;

    try {
        await reorderMentorQuestUnits(quests.value.map((quest) => quest.questUnitId));
    } catch {
        quests.value = previousQuests;
        emit('notify', mentorPersonalAssignmentSectionConfig.reorderFailed);
    } finally {
        isReordering.value = false;
    }
};

const onDrop = async (targetIndex: number): Promise<void> => {
    if (draggedIndex.value === null || props.disabled || isReordering.value) {
        onDragEnd();
        return;
    }

    const fromIndex = draggedIndex.value;
    onDragEnd();

    if (fromIndex === targetIndex) {
        return;
    }

    const previousQuests = [...quests.value];
    reorderLocalQuests(fromIndex, targetIndex);
    await persistUnitOrder(previousQuests);
};

const onDragEnd = (): void => {
    draggedIndex.value = null;
    dragOverIndex.value = null;
    dragFromHandle = false;
};

const onQuestTitleClick = async (questId: number): Promise<void> => {
    if (props.studentId === null || isNavigating.value) {
        return;
    }

    isNavigating.value = true;
    navigationError.value = '';

    try {
        await selectMentorStudent(props.studentId);
        emit('close');
        await router.push({
            name: 'student-quest-detail',
            params: { questId },
        });
    } catch (caughtError: unknown) {
        navigationError.value = extractApiErrorMessage(
            caughtError,
            'studentId',
            mentorStudentMessages.selectFailed,
        );
    } finally {
        isNavigating.value = false;
    }
};

watch(
    () => [props.open, props.studentId] as const,
    async ([isOpen, studentId]) => {
        if (!isOpen || studentId === null) {
            quests.value = [];
            expandedQuestUnitIds.value = [];
            error.value = null;
            navigationError.value = '';

            return;
        }

        await loadQuests();
    },
);
</script>
