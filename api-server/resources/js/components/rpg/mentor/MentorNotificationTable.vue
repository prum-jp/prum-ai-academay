<template>
    <RpgCard :title="mentorNotificationsConfig.cardTitle" :icon="mentorNotificationsConfig.cardIcon">
        <AsyncState
            :is-loading="isLoading"
            :error="error"
            :is-empty="items.length === 0"
            :loading-message="mentorNotificationsConfig.loading"
            :empty-message="mentorNotificationsConfig.empty"
        >
            <div class="mentor-notification-table-wrap">
                <table class="mentor-notification-table">
                    <thead>
                        <tr>
                            <th>{{ mentorNotificationsConfig.columns.name }}</th>
                            <th>{{ mentorNotificationsConfig.columns.type }}</th>
                            <th>{{ mentorNotificationsConfig.columns.requestedAt }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="`${item.studentId}-${item.questId}`">
                            <td>
                                <button
                                    type="button"
                                    class="mentor-notification-name-link"
                                    :disabled="disabled"
                                    @click="$emit('open', item)"
                                >
                                    {{ item.studentName }}
                                </button>
                            </td>
                            <td>{{ item.typeLabel }}</td>
                            <td>{{ formatDateTime(item.requestedAt) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </AsyncState>
    </RpgCard>
</template>

<script setup lang="ts">
import { mentorNotificationsConfig } from '@/constants/mentor/mentorNotifications';
import type { MentorReviewRequestItem } from '@/types/mentor/mentorNotifications';
import { formatDateTime } from '@/utils/shared/formatDateTime';
import AsyncState from '@/components/rpg/shared/AsyncState.vue';
import RpgCard from '@/components/rpg/shared/RpgCard.vue';

defineProps<{
    items: MentorReviewRequestItem[];
    isLoading: boolean;
    error: string;
    disabled?: boolean;
}>();

defineEmits<{
    open: [item: MentorReviewRequestItem];
}>();
</script>
