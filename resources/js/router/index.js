import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        name: 'dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: { auth: true },
    },
    {
        path: '/organizations',
        name: 'organizations',
        component: () => import('../views/Organizations.vue'),
        meta: { auth: true },
    },
    {
        path: '/employees',
        name: 'employees',
        component: () => import('../views/Employees.vue'),
        meta: { auth: true },
    },
    {
        path: '/programs',
        name: 'programs',
        component: () => import('../views/Programs.vue'),
        meta: { auth: true },
    },
    {
        path: '/groups',
        name: 'groups',
        component: () => import('../views/Groups.vue'),
        meta: { auth: true },
    },
    {
        path: '/notifications',
        name: 'notifications',
        component: () => import('../views/Notifications.vue'),
        meta: { auth: true },
    },
    {
        path: '/contracts',
        name: 'contracts',
        component: () => import('../views/Contracts.vue'),
        meta: { auth: true },
    },
    {
        path: '/admin/users',
        name: 'admin-users',
        component: () => import('../views/Users.vue'),
        meta: { auth: true, role: 'admin' },
    },
    {
        path: '/admin/activity-log',
        name: 'admin-activity-log',
        component: () => import('../views/ActivityLog.vue'),
        meta: { auth: true, role: 'admin' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from) => {
    const auth = useAuthStore();

    if (to.meta.auth && !auth.isAuthenticated) {
        return '/login';
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return '/';
    }

    return true;
});

export default router;