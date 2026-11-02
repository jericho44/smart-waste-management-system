<template>
  <div class="space-y-6">
    <div class="text-center">
      <h3 class="text-xl font-bold text-slate-900">Create Citizen Account</h3>
      <p class="text-xs text-slate-500 mt-1">Submit and track community waste reports</p>
    </div>

    <!-- Alert / Generic Error -->
    <div v-if="generalError" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center gap-2">
      <span class="font-bold">⚠️</span>
      <span>{{ generalError }}</span>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
          Full Name
        </label>
        <input
          id="name"
          v-model="name"
          type="text"
          required
          :class="[
            'w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:bg-white',
            errors.name ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500'
          ]"
          placeholder="Jane Citizen"
        />
        <p v-if="errors.name" class="text-xs text-red-600 mt-1">{{ errors.name[0] }}</p>
      </div>

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
          placeholder="citizen@example.com"
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
          autocomplete="new-password"
          required
          :class="[
            'w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:bg-white',
            errors.password ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500'
          ]"
          placeholder="Minimum 8 characters"
        />
        <p v-if="errors.password" class="text-xs text-red-600 mt-1">{{ errors.password[0] }}</p>
      </div>

      <div>
        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
          Confirm Password
        </label>
        <input
          id="password_confirmation"
          v-model="passwordConfirmation"
          type="password"
          autocomplete="new-password"
          required
          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white"
          placeholder="Repeat password"
        />
      </div>

      <button
        type="submit"
        :disabled="isLoading"
        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold rounded-xl text-sm transition shadow-sm shadow-emerald-200 flex justify-center items-center gap-2 cursor-pointer"
      >
        <span v-if="isLoading" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full"></span>
        <span>{{ isLoading ? 'Creating account...' : 'Create Account' }}</span>
      </button>
    </form>

    <div class="space-y-3 pt-3 border-t border-slate-100 text-center text-xs text-slate-500">
      <p>
        Already registered?
        <router-link to="/login" class="text-emerald-600 font-semibold hover:underline">
          Sign in here
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
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const isLoading = ref(false);
const generalError = ref('');
const errors = ref<Record<string, string[]>>({});

const handleSubmit = async () => {
  isLoading.value = true;
  generalError.value = '';
  errors.value = {};

  try {
    await authStore.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });

    router.push('/dashboard');
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else if (error.response?.data?.message) {
      generalError.value = error.response.data.message;
    } else {
      generalError.value = 'Failed to register account. Please check the form data.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

