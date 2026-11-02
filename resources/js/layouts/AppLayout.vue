<template>
  <div class="min-h-screen bg-slate-50 flex font-sans antialiased text-slate-800">
    <!-- Mobile Backdrop Overlay -->
    <transition
      enter-active-class="transition-opacity duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isMobileSidebarOpen"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden"
        @click="isMobileSidebarOpen = false"
      ></div>
    </transition>

    <!-- Sidebar Navigation -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col justify-between transition-transform duration-300 ease-in-out lg:translate-x-0',
        isMobileSidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'
      ]"
    >
      <!-- Sidebar Top: Brand & Navigation -->
      <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
          <router-link to="/" class="flex items-center space-x-3 group" @click="isMobileSidebarOpen = false">
            <div class="h-9 w-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-emerald-200 group-hover:bg-emerald-700 transition">
              🌱
            </div>
            <div>
              <span class="text-base font-black text-slate-900 tracking-tight">EcoSmart</span>
              <span class="ml-1.5 px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">System</span>
            </div>
          </router-link>

          <!-- Mobile Close Button -->
          <button
            @click="isMobileSidebarOpen = false"
            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg lg:hidden"
            title="Tutup Menu"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Create Report Quick Action Button -->
        <div class="p-4" v-if="authStore.isAuthenticated">
          <router-link
            to="/reports/create"
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-emerald-200 transition transform hover:-translate-y-0.5"
            @click="isMobileSidebarOpen = false"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Buat Laporan Sampah</span>
          </router-link>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 space-y-6 pt-2">
          <!-- Main Menu Section -->
          <div>
            <p class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
              Menu Utama
            </p>
            <div class="space-y-1">
              <!-- Home -->
              <router-link
                to="/"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition"
                active-class="bg-emerald-50 text-emerald-700 font-bold border border-emerald-200/60"
                exact
                @click="isMobileSidebarOpen = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Beranda (Home)</span>
              </router-link>

              <!-- Dashboard (Authenticated only) -->
              <router-link
                v-if="authStore.isAuthenticated"
                to="/dashboard"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition"
                active-class="bg-emerald-50 text-emerald-700 font-bold border border-emerald-200/60"
                @click="isMobileSidebarOpen = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Dashboard</span>
              </router-link>

              <!-- Reports (Authenticated only) -->
              <router-link
                v-if="authStore.isAuthenticated"
                to="/reports"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition"
                active-class="bg-emerald-50 text-emerald-700 font-bold border border-emerald-200/60"
                @click="isMobileSidebarOpen = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Laporan Sampah</span>
              </router-link>
            </div>
          </div>

          <!-- Administration Section (Admin only) -->
          <div v-if="authStore.isAdmin">
            <p class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
              Administrasi Sistem
            </p>
            <div class="space-y-1">
              <!-- Categories -->
              <router-link
                to="/admin/categories"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition"
                active-class="bg-emerald-50 text-emerald-700 font-bold border border-emerald-200/60"
                @click="isMobileSidebarOpen = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>Kategori Sampah</span>
              </router-link>

              <!-- Users -->
              <router-link
                to="/admin/users"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition"
                active-class="bg-emerald-50 text-emerald-700 font-bold border border-emerald-200/60"
                @click="isMobileSidebarOpen = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Manajemen Pengguna</span>
              </router-link>
            </div>
          </div>

          <!-- Guest Section (Unauthenticated) -->
          <div v-if="!authStore.isAuthenticated" class="pt-4 border-t border-slate-100">
            <p class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
              Akun & Masuk
            </p>
            <div class="space-y-2 px-1">
              <router-link
                to="/login"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
                @click="isMobileSidebarOpen = false"
              >
                <span>Masuk (Sign In)</span>
              </router-link>
              <router-link
                to="/register"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-xs"
                @click="isMobileSidebarOpen = false"
              >
                <span>Daftar Akun</span>
              </router-link>
            </div>
          </div>
        </nav>
      </div>

      <!-- Sidebar Bottom: User Profile & Logout -->
      <div v-if="authStore.isAuthenticated" class="p-3 border-t border-slate-100 bg-slate-50/70">
        <div class="p-2.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs space-y-2.5">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold shrink-0">
              {{ userInitials }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-xs font-bold text-slate-800 truncate leading-tight">{{ authStore.user?.name }}</p>
              <p class="text-[10px] text-slate-400 font-medium truncate capitalize">{{ userPrimaryRole }}</p>
            </div>
          </div>

          <!-- Profile & Logout Buttons -->
          <div class="grid grid-cols-2 gap-1.5 pt-1.5 border-t border-slate-100">
            <router-link
              to="/profile"
              class="flex items-center justify-center gap-1.5 px-2 py-1.5 text-[11px] font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition"
              @click="isMobileSidebarOpen = false"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>Profil</span>
            </router-link>

            <button
              @click="handleLogout"
              class="flex items-center justify-center gap-1.5 px-2 py-1.5 text-[11px] font-semibold text-slate-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span>Keluar</span>
            </button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
      <!-- Minimalist Top Bar -->
      <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Left: Mobile Menu Toggle Button & Page Context -->
        <div class="flex items-center gap-3">
          <button
            @click="isMobileSidebarOpen = true"
            class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl lg:hidden cursor-pointer"
            title="Buka Navigasi"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Current Breadcrumb / System Name -->
          <div class="hidden sm:flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-400">Smart Waste Management System</span>
            <span class="text-slate-300">/</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              <span>Online & Terhubung</span>
            </span>
          </div>
        </div>

        <!-- Right: Fast Actions / Profile Icon Shortcut -->
        <div class="flex items-center gap-3">
          <!-- Report button shortcut if authenticated -->
          <router-link
            v-if="authStore.isAuthenticated"
            to="/reports/create"
            class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition"
          >
            <span>+ Lapor Sampah</span>
          </router-link>

          <!-- Profile Badge -->
          <div v-if="authStore.isAuthenticated" class="flex items-center gap-2">
            <router-link to="/profile" class="flex items-center gap-2 hover:opacity-80 transition" title="Lihat Profil">
              <div class="h-8 w-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold shadow-2xs">
                {{ userInitials }}
              </div>
              <div class="hidden md:block text-left text-xs">
                <p class="font-bold text-slate-800 leading-tight">{{ authStore.user?.name }}</p>
                <p class="text-[10px] text-slate-400 capitalize">{{ userPrimaryRole }}</p>
              </div>
            </router-link>
          </div>

          <!-- Guest Actions -->
          <div v-else class="flex items-center gap-2">
            <router-link
              to="/login"
              class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:text-emerald-600 transition"
            >
              Masuk
            </router-link>
            <router-link
              to="/register"
              class="px-3.5 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition"
            >
              Daftar
            </router-link>
          </div>
        </div>
      </header>

      <!-- Main View Content -->
      <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <router-view />
      </main>

      <!-- Footer -->
      <footer class="bg-white border-t border-slate-200/80 py-5 text-center text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4">
          &copy; 2026 Smart Waste Management System. All rights reserved. Built with Laravel 12 & Vue 3.
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const isMobileSidebarOpen = ref(false);

// Automatically close mobile sidebar on navigation
router.afterEach(() => {
  isMobileSidebarOpen.value = false;
});

const userInitials = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.slice(0, 2).toUpperCase();
});

const userPrimaryRole = computed(() => {
  if (authStore.isAdmin) return 'Administrator';
  if (authStore.isCitizen) return 'Warga / Citizen';
  return 'User';
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
