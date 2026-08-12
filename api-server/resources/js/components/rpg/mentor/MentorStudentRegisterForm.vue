<template>
    <form class="mentor-register-form" @submit.prevent="onSubmit">
        <div class="input-group">
            <label for="register-name">{{ mentorStudentFormLabels.name }}</label>
            <input
                id="register-name"
                v-model="form.name"
                type="text"
                required
                maxlength="255"
                :placeholder="mentorStudentFormPlaceholders.name"
                autocomplete="name"
            />
            <p v-if="fieldErrors.name" class="login-error">{{ fieldErrors.name }}</p>
        </div>

        <div class="input-group">
            <label for="register-email">{{ mentorStudentFormLabels.email }}</label>
            <input
                id="register-email"
                v-model="form.email"
                type="email"
                required
                maxlength="255"
                :placeholder="mentorStudentFormPlaceholders.email"
                autocomplete="email"
            />
            <p v-if="fieldErrors.email" class="login-error">{{ fieldErrors.email }}</p>
        </div>

        <div class="input-group">
            <label for="register-password">{{ mentorStudentFormLabels.password }}</label>
            <input
                id="register-password"
                v-model="form.password"
                type="password"
                required
                minlength="8"
                :placeholder="mentorStudentFormPlaceholders.password"
                autocomplete="new-password"
            />
            <p v-if="fieldErrors.password" class="login-error">{{ fieldErrors.password }}</p>
        </div>

        <div class="input-group">
            <label for="register-password-confirmation">
                {{ mentorStudentFormLabels.passwordConfirmation }}
            </label>
            <input
                id="register-password-confirmation"
                v-model="form.password_confirmation"
                type="password"
                required
                minlength="8"
                :placeholder="mentorStudentFormPlaceholders.passwordConfirmation"
                autocomplete="new-password"
            />
            <p v-if="fieldErrors.password_confirmation" class="login-error">
                {{ fieldErrors.password_confirmation }}
            </p>
        </div>

        <div class="input-group">
            <label for="register-role">{{ mentorStudentFormLabels.role }}</label>
            <select id="register-role" v-model.number="form.role" required>
                <option
                    v-for="option in mentorStudentRoleOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <p v-if="fieldErrors.role" class="login-error">{{ fieldErrors.role }}</p>
        </div>

        <p class="mentor-register-note">{{ mentorStudentMessages.registerNote }}</p>

        <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

        <div class="action-area mentor-register-actions">
            <RpgButton type="submit" icon="fa-solid fa-user-plus" :disabled="isSubmitting">
                {{
                    isSubmitting
                        ? mentorStudentMessages.registerSubmittingLabel
                        : mentorStudentMessages.registerSubmitLabel
                }}
            </RpgButton>
        </div>
    </form>
</template>

<script setup lang="ts">
import type { MentorStudent } from '@/types/mentor/mentor';
import {
    mentorStudentFormLabels,
    mentorStudentFormPlaceholders,
    mentorStudentMessages,
    mentorStudentRoleOptions,
} from '@/constants/mentor/mentor';
import { useMentorStudentRegister } from '@/composables/mentor/useMentorStudentRegister';
import RpgButton from '@/components/rpg/shared/RpgButton.vue';

const emit = defineEmits<{
    created: [student: MentorStudent];
}>();

const { form, isSubmitting, errorMessage, fieldErrors, submit } = useMentorStudentRegister();

const onSubmit = async (): Promise<void> => {
    const student = await submit();
    if (!student) {
        return;
    }

    emit('created', student);
};
</script>
