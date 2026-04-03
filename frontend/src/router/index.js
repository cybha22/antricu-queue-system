import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/auth/LoginView.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('../components/layout/AppLayout.vue'),
        meta: { auth: true },
        children: [
            { path: '', redirect: '/dashboard' },
            {
                path: 'dashboard',
                name: 'Dashboard',
                component: () => import('../views/admin/DashboardView.vue'),
                meta: { role: 'admin' },
            },
            {
                path: 'queue-call',
                name: 'QueueCall',
                component: () => import('../views/operator/QueueCallView.vue'),
            },
            {
                path: 'counters',
                name: 'Counters',
                component: () => import('../views/admin/CountersView.vue'),
                meta: { role: 'admin' },
            },
            {
                path: 'queues',
                name: 'Queues',
                component: () => import('../views/admin/QueuesView.vue'),
                meta: { role: 'admin' },
            },
            {
                path: 'services',
                name: 'Services',
                component: () => import('../views/admin/ServicesView.vue'),
                meta: { role: 'admin' },
            },
            {
                path: 'settings',
                name: 'Settings',
                component: () => import('../views/admin/SettingsView.vue'),
                meta: { role: 'admin' },
            },
            {
                path: 'users',
                name: 'Users',
                component: () => import('../views/admin/UsersView.vue'),
                meta: { role: 'admin' },
            },
            {
                path: 'reports',
                name: 'Reports',
                component: () => import('../views/admin/ReportView.vue'),
                meta: { role: 'admin' },
            },
        ],
    },
    {
        path: '/kiosk/display',
        name: 'KioskDisplay',
        component: () => import('../views/kiosk/DisplayView.vue'),
    },
    {
        path: '/kiosk/print',
        name: 'KioskPrint',
        component: () => import('../views/kiosk/PrintView.vue'),
    },
    {
        path: '/kiosk/status/:number',
        name: 'KioskStatus',
        component: () => import('../views/kiosk/StatusView.vue'),
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

let authChecked = false

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()

    if (!authChecked) {
        authChecked = true
        await authStore.fetchUser()
    }

    const isLoggedIn = !!authStore.user
    const requiresAuth = to.matched.some(r => r.meta.auth)
    const isGuestOnly = to.meta.guest

    if (requiresAuth && !isLoggedIn) {
        return next('/login')
    }

    if (isGuestOnly && isLoggedIn) {
        return next('/')
    }

    if (to.meta.role && authStore.user?.role !== to.meta.role) {
        return next('/queue-call')
    }

    next()
})

export default router
