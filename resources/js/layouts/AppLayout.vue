<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans">
    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo & Brand -->
          <div class="flex items-center space-x-3">
            <router-link to="/" class="flex items-center space-x-3 group">
              <div class="h-10 w-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xl shadow-sm shadow-emerald-200 group-hover:bg-emerald-700 transition">
                🌱
              </div>
              <div>
                <span class="text-lg font-bold text-slate-900 tracking-tight">EcoSmart Waste</span>
                <span class="hidden sm:inline-block ml-2 px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">System</span>
              </div>
            </router-link>
          </div>

          <!-- Navigation Links -->
          <nav class="flex items-center space-x-4 sm:space-x-6 text-sm font-medium">
            <router-link
              to="/"
              class="text-slate-600 hover:text-emerald-600 transition"
              active-class="text-emerald-600 font-semibold"
            >
              Home
            </router-link>

            <router-link
              v-if="authStore.isAuthenticated"
              to="/dashboard"
              class="text-slate-600 hover:text-emerald-600 transition"
              active-class="text-emerald-600 font-semibold"
            >
              Dashboard
            </router-link>

            <!-- Admin Management Links -->
            <router-link
              v-if="authStore.isAdmin"
              to="/admin/categories"
              class="text-slate-600 hover:text-emerald-600 transition"
              active-class="text-emerald-600 font-semibold"
            >
              Categories
            </router-link>

            <router-link
              v-if="authStore.isAdmin"
              to="/admin/users"
              class="text-slate-600 hover:text-emerald-600 transition"
              active-class="text-emerald-600 font-semibold"
            >
              Users
            </router-link>

            <!-- Authenticated User Menu -->
            <div v-if="authStore.isAuthenticated" class="flex items-center space-x-3 pl-2 sm:pl-4 border-l border-slate-200">
              <router-link
                to="/profile"
                class="flex items-center space-x-2 text-slate-700 hover:text-emerald-600 transition"
                title="View Profile"
              >
                <div class="h-8 w-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold">
                  {{ userInitials }}
                </div>
                <div class="hidden md:block text-left text-xs">
                  <p class="font-semibold text-slate-800 leading-tight">{{ authStore.user?.name }}</p>
                  <p class="text-slate-400 capitalize">{{ userPrimaryRole }}</p>
                </div>
              </router-link>

              <button
                @click="handleLogout"
                class="p-2 text-slate-400 hover:text-red-600 rounded-lg transition cursor-pointer"
                title="Sign Out"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
              </button>
            </div>

            <!-- Guest Links -->
            <div v-else class="flex items-center space-x-2 sm:space-x-3">
              <router-link
                to="/login"
                class="px-3.5 py-1.5 text-slate-700 hover:text-emerald-600 font-semibold text-sm transition"
              >
                Sign In
              </router-link>
              <router-link
                to="/register"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs shadow-emerald-200 font-semibold text-sm transition cursor-pointer"
              >
                Register
              </router-link>
            </div>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
      <div class="max-w-7xl mx-auto px-4">
        &copy; 2026 Smart Waste Management System. All rights reserved. Built with Laravel 12 & Vue 3.
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const userInitials = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.slice(0, 2).toUpperCase();
});

const userPrimaryRole = computed(() => {
  if (authStore.isAdmin) return 'Admin';
  if (authStore.isCitizen) return 'Citizen';
  return 'User';
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
