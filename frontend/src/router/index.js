import { createRouter, createWebHistory } from 'vue-router';
import authService from '../services/auth_service';

// Import views
import EventDashboard from '../components/EventDashboard.vue';
import LoginView from '../components/LoginView.vue';
import RegisterView from '../components/RegisterView.vue';
import MyEventsView from '../components/MyEventsView.vue';
import EventDetailView from '../components/EventDetailView.vue';
import NotFoundView from '../components/NotFoundView.vue';

const routes = [
    {
        path: '/',
        redirect: '/events'
    },
    {
        path: '/events',
        name: 'events',
        component: EventDashboard
    },
    {
        path: '/events/:id',
        name: 'event-detail',
        component: EventDetailView,
        props: true
    },
    {
        path: '/my-events',
        name: 'my-events',
        component: MyEventsView,
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { guestOnly: true }
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterView,
        meta: { guestOnly: true }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFoundView
    }
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
});

// Navigation guards
router.beforeEach((to, from, next) => {
    const isAuthenticated = authService.isAuthenticated();

    // Route requires authentication
    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'login' });
    }
    // Route is for guests only (like login, register) and user is authenticated
    else if (to.meta.guestOnly && isAuthenticated) {
        next({ name: 'events' });
    }
    // Proceed as normal
    else {
        next();
    }
});

export default router;