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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();

    if (to.meta.auth && !auth.isAuthenticated) {
        return next('/login');
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return next('/');
    }

    next();
});

export default router;