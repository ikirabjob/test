<template>
  <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <div class="flex-shrink-0 flex items-center">
            <router-link to="/" class="text-2xl font-bold text-blue-600">
              EventHub
            </router-link>
          </div>
          <nav class="ml-6 flex space-x-8">
            <router-link
                to="/events"
                class="inline-flex items-center px-1 pt-1 text-gray-900 hover:text-blue-600"
            >
              Events
            </router-link>
            <router-link
                v-if="isAuthenticated"
                to="/my-events"
                class="inline-flex items-center px-1 pt-1 text-gray-900 hover:text-blue-600"
            >
              My Events
            </router-link>
          </nav>
        </div>
        <div class="flex items-center">
          <template v-if="isAuthenticated">
            <span class="text-gray-700 mr-4">{{ userFullName }}</span>
            <button
                @click="handleLogout"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              Logout
            </button>
          </template>
          <template v-else>
            <router-link
                to="/login"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 mr-2"
            >
              Login
            </router-link>
            <router-link
                to="/register"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              Register
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import authService from '../services/auth_service';

const router = useRouter();
const currentUser = ref(null);

const isAuthenticated = computed(() => {
  return authService.isAuthenticated();
});

const userFullName = computed(() => {
  if (!currentUser.value) return '';
  return `${currentUser.value.firstName} ${currentUser.value.lastName}`;
});

const handleLogout = () => {
  authService.logout();
  router.push('/login');
};

onMounted(async () => {
  if (isAuthenticated.value) {
    try {
      currentUser.value = await authService.getCurrentUser();
    } catch (error) {
      console.error('Failed to fetch user data:', error);
      authService.logout();
    }
  }
});
</script>