<template>
  <div class="space-y-6">
    <div class="text-center">
      <h3 class="text-xl font-bold text-slate-900">Sign in to your account</h3>
      <p class="text-xs text-slate-500 mt-1">Access waste reports, management, and analytics</p>
    </div>

    <!-- Alert / Generic Error -->
    <div v-if="generalError" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center gap-2">
      <span class="font-bold">⚠️</span>
      <span>{{ generalError }}</span>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
          Email address
        </label>
        <input
          id="email"
          v-model="email"
          type="email"
          autocomplete="email"
          required
          :class="[
            'w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:bg-white',
            errors.email ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500'
          ]"
          placeholder="admin@waste.local or citizen email"
        />
        <p v-if="errors.email" class="text-xs text-red-600 mt-1">{{ errors.email[0] }}</p>
      </div>

      <div>
        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
          Password
        </label>
        <input
          id="password"
          v-model="password"
          type="password"
          autocomplete="current-password"
          required
          :class="[
            'w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:bg-white',
            errors.password ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500'
          ]"
          placeholder="••••••••"
        />
        <p v-if="errors.password" class="text-xs text-red-600 mt-1">{{ errors.password[0] }}</p>
      </div>

      <div class="flex items-center justify-between text-xs">
        <label class="flex items-center text-slate-600 cursor-pointer">
          <input type="checkbox" v-model="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
          <span class="ml-2">Remember this device</span>
        </label>
      </div>

      <button
        type="submit"
        :disabled="isLoading"
        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold rounded-xl text-sm transition shadow-sm shadow-emerald-200 flex justify-center items-center gap-2 cursor-pointer"
      >
        <span v-if="isLoading" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full"></span>
        <span>{{ isLoading ? 'Authenticating...' : 'Sign In' }}</span>
      </button>
    </form>

    <div class="space-y-3 pt-3 border-t border-slate-100 text-center text-xs text-slate-500">
      <p>
        Don't have an account?
        <router-link to="/register" class="text-emerald-600 font-semibold hover:underline">
          Register as citizen
        </router-link>
      </p>
      <p>
        <router-link to="/" class="text-slate-500 hover:text-slate-700">
          &larr; Back to Home
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const remember = ref(false);
const isLoading = ref(false);
const generalError = ref('');
const errors = ref<Record<string, string[]>>({});

const handleSubmit = async () => {
  isLoading.value = true;
  generalError.value = '';
  errors.value = {};

  try {
    await authStore.login({
      email: email.value,
      password: password.value,
      device_name: remember.value ? 'browser-remembered' : 'web',
    });

    const redirectPath = (route.query.redirect as string) || '/dashboard';
    router.push(redirectPath);
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else if (error.response?.data?.message) {
      generalError.value = error.response.data.message;
    } else {
      generalError.value = 'Failed to connect to authentication service. Please check your credentials.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>
