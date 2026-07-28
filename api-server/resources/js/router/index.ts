import { createRouter, createWebHistory } from 'vue-router';
import StudentLayout from '@/layouts/StudentLayout.vue';
import StudentPage from '@/pages/StudentPage.vue';
import StudentQuestsPage from '@/pages/StudentQuestsPage.vue';
import StudentSkillbookPage from '@/pages/StudentSkillbookPage.vue';
import MentorPage from '@/pages/MentorPage.vue';
import MentorQuestsPage from '@/pages/MentorQuestsPage.vue';
import MentorToolsPage from '@/pages/MentorToolsPage.vue';
import MentorStudentRegisterPage from '@/pages/MentorStudentRegisterPage.vue';
import LoginPage from '@/pages/LoginPage.vue';
import { useAuth } from '@/composables/useAuth';
import { ROLE_MENTOR, ROLE_STUDENT } from '@/types/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: LoginPage,
            meta: { guest: true },
        },
        {
            path: '/',
            component: StudentLayout,
            meta: { requiresAuth: true, roles: [ROLE_STUDENT, ROLE_MENTOR] },
            children: [
                {
                    path: '',
                    name: 'student-sheet',
                    component: StudentPage,
                },
                {
                    path: 'quests',
                    name: 'student-quests',
                    component: StudentQuestsPage,
                },
                {
                    path: 'skillbook',
                    name: 'student-skillbook',
                    component: StudentSkillbookPage,
                },
            ],
        },
        {
            path: '/mentor',
            name: 'mentor',
            component: MentorPage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
        {
            path: '/mentor/register',
            name: 'mentor-register',
            component: MentorStudentRegisterPage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
        {
            path: '/mentor/quests',
            name: 'mentor-quests',
            component: MentorQuestsPage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
        {
            path: '/mentor/tools',
            name: 'mentor-tools',
            component: MentorToolsPage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
    ],
});

router.beforeEach(async (to) => {
    const { user, initialized, fetchUser, homePathFor } = useAuth();

    if (!initialized.value) {
        await fetchUser();
    }

    if (to.meta.guest === true && user.value) {
        return homePathFor(user.value);
    }

    if (to.meta.requiresAuth === true && !user.value) {
        return { name: 'login' };
    }

    const allowedRoles = to.meta.roles;
    if (
        to.meta.requiresAuth === true &&
        user.value &&
        Array.isArray(allowedRoles) &&
        allowedRoles.length > 0 &&
        !allowedRoles.includes(Number(user.value.role))
    ) {
        return homePathFor(user.value);
    }

    return true;
});

export default router;
