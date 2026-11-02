<template>
  <div class="max-w-4xl mx-auto space-y-8">
    <div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight">Account Profile</h1>
      <p class="text-xs text-slate-500 mt-1">Manage your account credentials and system settings</p>
    </div>

    <!-- User Info Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="h-16 w-16 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-2xl">
          {{ userInitials }}
        </div>
        <div>
          <h2 class="text-lg font-bold text-slate-900">{{ authStore.user?.name || 'User' }}</h2>
          <p class="text-xs text-slate-500">{{ authStore.user?.email }}</p>
          <div class="mt-2 flex flex-wrap gap-1.5">
            <span
              v-for="role in authStore.user?.roles"
              :key="role"
              class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 uppercase tracking-wider"
            >
              {{ role }}
            </span>
          </div>
        </div>
      </div>

      <div class="text-xs text-slate-400 space-y-1">
        <p>Member since: <span class="text-slate-600 font-medium">{{ formatDate(authStore.user?.created_at) }}</span></p>
        <p>Account status: <span class="text-emerald-600 font-medium">Active</span></p>
      </div>
    </div>

    <!-- Forms Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Edit Profile -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Personal Information</h3>

        <div v-if="profileSuccess" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl">
          {{ profileSuccess }}
        </div>
        <div v-if="profileError" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl">
          {{ profileError }}
        </div>

        <form @submit.prevent="handleUpdateProfile" class="space-y-4">
          <div>
            <label for="profile_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
              Full Name
            </label>
            <input
              id="profile_name"
              v-model="profileForm.name"
              type="text"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
            />
          </div>

          <div>
            <label for="profile_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
              Email Address
            </label>
            <input
              id="profile_email"
              v-model="profileForm.email"
              type="email"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
            />
          </div>

          <button
            type="submit"
            :disabled="isUpdatingProfile"
            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold rounded-xl text-sm transition shadow-sm cursor-pointer"
          >
            {{ isUpdatingProfile ? 'Saving...' : 'Save Profile Changes' }}
          </button>
        </form>
      </div>

      <!-- Change Password -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Security & Password</h3>

        <div v-if="passwordSuccess" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl">
          {{ passwordSuccess }}
        </div>
        <div v-if="passwordError" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl">
          {{ passwordError }}
        </div>

        <form @submit.prevent="handleChangePassword" class="space-y-4">
          <div>
            <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
              Current Password
            </label>
            <input
              id="current_password"
              v-model="passwordForm.current_password"
              type="password"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
              placeholder="••••••••"
            />
          </div>

          <div>
            <label for="new_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
              New Password
            </label>
            <input
              id="new_password"
              v-model="passwordForm.new_password"
              type="password"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
              placeholder="Min 8 characters"
            />
          </div>

          <div>
            <label for="new_password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
              Confirm New Password
            </label>
            <input
              id="new_password_confirmation"
              v-model="passwordForm.new_password_confirmation"
              type="password"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"
              placeholder="Repeat new password"
            />
          </div>

          <button
            type="submit"
            :disabled="isChangingPassword"
            class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-900 disabled:opacity-50 text-white font-semibold rounded-xl text-sm transition shadow-sm cursor-pointer"
          >
            {{ isChangingPassword ? 'Updating...' : 'Change Password' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import dayjs from 'dayjs';

const authStore = useAuthStore();

const profileForm = ref({
  name: '',
  email: '',
});

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
});

const isUpdatingProfile = ref(false);
const profileSuccess = ref('');
const profileError = ref('');

const isChangingPassword = ref(false);
const passwordSuccess = ref('');
const passwordError = ref('');

onMounted(() => {
  if (authStore.user) {
    profileForm.value.name = authStore.user.name;
    profileForm.value.email = authStore.user.email;
  }
});

const userInitials = computed(() => {
  const name = authStore.user?.name || 'User';
  return name.slice(0, 2).toUpperCase();
});

const formatDate = (date?: string) => {
  if (!date) return '-';
  return dayjs(date).format('MMM D, YYYY');
};

const handleUpdateProfile = async () => {
  isUpdatingProfile.value = true;
  profileSuccess.value = '';
  profileError.value = '';

  try {
    await authStore.updateProfile(profileForm.value);
    profileSuccess.value = 'Profile updated successfully!';
  } catch (err: any) {
    profileError.value = err.response?.data?.message || 'Failed to update profile.';
  } finally {
    isUpdatingProfile.value = false;
  }
};

const handleChangePassword = async () => {
  isChangingPassword.value = true;
  passwordSuccess.value = '';
  passwordError.value = '';

  try {
    await authStore.changePassword(passwordForm.value);
    passwordSuccess.value = 'Password changed successfully!';
    passwordForm.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    };
  } catch (err: any) {
    passwordError.value = err.response?.data?.message || 'Failed to change password.';
  } finally {
    isChangingPassword.value = false;
  }
};
</script>

