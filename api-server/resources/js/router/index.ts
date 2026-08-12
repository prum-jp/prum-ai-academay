import { createRouter, createWebHistory } from 'vue-router';
import StudentLayout from '@/layouts/StudentLayout.vue';
import StudentPage from '@/pages/student/StudentPage.vue';
import StudentQuestsPage from '@/pages/student/StudentQuestsPage.vue';
import StudentQuestDetailPage from '@/pages/student/StudentQuestDetailPage.vue';
// TODO: 後に機能追加 — 実績バッジ（スキルブック）
// import StudentSkillbookPage from '@/pages/student/StudentSkillbookPage.vue';
import StudentDirectoryPage from '@/pages/student/StudentDirectoryPage.vue';
import StudentDetailPage from '@/pages/student/StudentDetailPage.vue';
import MentorNotificationsPage from '@/pages/mentor/MentorNotificationsPage.vue';
import MentorQuestsPage from '@/pages/mentor-quest/MentorQuestsPage.vue';
import MentorQuestMasterPage from '@/pages/mentor-master/MentorQuestMasterPage.vue';
import MentorQuestDetailPage from '@/pages/mentor-master/MentorQuestDetailPage.vue';
import MentorQuestEditPage from '@/pages/mentor-master/MentorQuestEditPage.vue';
import MentorQuestUnitDetailPage from '@/pages/mentor-master/MentorQuestUnitDetailPage.vue';
import MentorQuestUnitEditPage from '@/pages/mentor-master/MentorQuestUnitEditPage.vue';
import MentorQuestCreatePage from '@/pages/mentor-quest/MentorQuestCreatePage.vue';
import MentorToolsPage from '@/pages/mentor-tools/MentorToolsPage.vue';
import MentorStudentRegisterPage from '@/pages/mentor/MentorStudentRegisterPage.vue';
import LoginPage from '@/pages/auth/LoginPage.vue';
import { useAuth } from '@/composables/shared/useAuth';
import { ROLE_MENTOR, ROLE_STUDENT } from '@/types/shared/auth';

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
                    path: 'quests/:questId',
                    name: 'student-quest-detail',
                    component: StudentQuestDetailPage,
                    meta: { studentPage: 'student-quests' },
                },
                // TODO: 後に機能追加 — 実績バッジ（スキルブック）
                // {
                //     path: 'skillbook',
                //     name: 'student-skillbook',
                //     component: StudentSkillbookPage,
                // },
                {
                    path: 'students',
                    name: 'student-directory',
                    component: StudentDirectoryPage,
                },
                {
                    path: 'students/:studentId',
                    name: 'student-detail',
                    component: StudentDetailPage,
                    meta: { studentPage: 'student-directory', singleColumn: false },
                },
            ],
        },
        {
            path: '/mentor',
            redirect: { name: 'mentor-quests' },
        },
        {
            path: '/mentor/notifications',
            name: 'mentor-notifications',
            component: MentorNotificationsPage,
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
            path: '/mentor/quests/master',
            name: 'mentor-quest-master',
            component: MentorQuestMasterPage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
        {
            path: '/mentor/quests/new',
            name: 'mentor-quest-create',
            component: MentorQuestCreatePage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
        {
            path: '/mentor/quests/:questId/edit',
            name: 'mentor-quest-edit',
            component: MentorQuestEditPage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
        {
            path: '/mentor/quests/:questId',
            name: 'mentor-quest-detail',
            component: MentorQuestDetailPage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
        {
            path: '/mentor/quest-units/:unitId/edit',
            name: 'mentor-quest-unit-edit',
            component: MentorQuestUnitEditPage,
            meta: { requiresAuth: true, roles: [ROLE_MENTOR] },
        },
        {
            path: '/mentor/quest-units/:unitId',
            name: 'mentor-quest-unit-detail',
            component: MentorQuestUnitDetailPage,
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
