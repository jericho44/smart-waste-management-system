<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">User Management</h1>
        <p class="text-xs text-slate-500 mt-1">Manage system administrator and citizen accounts</p>
      </div>

      <button
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-emerald-200 transition cursor-pointer"
      >
        <span>+ Add User</span>
      </button>
    </div>

    <!-- Feedback Banner -->
    <div v-if="feedbackMessage" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex justify-between items-center">
      <span>{{ feedbackMessage }}</span>
      <button @click="feedbackMessage = ''" class="text-emerald-600 font-bold ml-2">&times;</button>
    </div>

    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex justify-between items-center">
      <span>{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="text-red-600 font-bold ml-2">&times;</button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="w-full sm:w-72 relative">
        <input
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Search name or email..."
          class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white"
        />
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <select
          v-model="roleFilter"
          @change="loadUsers"
          class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500"
        >
          <option value="">All Roles</option>
          <option value="admin">Administrator</option>
          <option value="user">Citizen User</option>
        </select>

        <select
          v-model="statusFilter"
          @change="loadUsers"
          class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500"
        >
          <option value="">All Statuses</option>
          <option value="1">Active Only</option>
          <option value="0">Inactive Only</option>
        </select>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4">User</th>
              <th class="py-3.5 px-4">Email</th>
              <th class="py-3.5 px-4">Role</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4">Joined Date</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="isLoading" class="text-center">
              <td colspan="6" class="py-12 text-slate-400">Loading users...</td>
            </tr>
            <tr v-else-if="users.length === 0" class="text-center">
              <td colspan="6" class="py-12 text-slate-400">No users found matching criteria.</td>
            </tr>
            <tr
              v-else
              v-for="u in users"
              :key="u.uuid"
              class="hover:bg-slate-50/70 transition"
            >
              <td class="py-3.5 px-4 font-semibold text-slate-900">
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ u.name.slice(0, 2).toUpperCase() }}
                  </div>
                  <div>
                    <span class="block text-slate-900">{{ u.name }}</span>
                    <span v-if="u.uuid === authStore.user?.uuid" class="text-[10px] text-emerald-600 font-bold">(You)</span>
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-4 text-slate-600 font-mono">
                {{ u.email }}
              </td>
              <td class="py-3.5 px-4">
                <span
                  v-for="r in u.roles"
                  :key="r"
                  :class="[
                    'px-2 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider',
                    r === 'admin' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200'
                  ]"
                >
                  {{ r }}
                </span>
              </td>
              <td class="py-3.5 px-4">
                <button
                  :disabled="u.uuid === authStore.user?.uuid"
                  @click="toggleStatus(u)"
                  :class="u.uuid === authStore.user?.uuid ? 'opacity-80 cursor-not-allowed' : 'cursor-pointer'"
                  title="Toggle active status"
                >
                  <StatusBadge :active="u.is_active" />
                </button>
              </td>
              <td class="py-3.5 px-4 text-slate-500">
                {{ formatDate(u.created_at) }}
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <button
                  @click="openEditModal(u)"
                  class="px-2.5 py-1 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition font-medium cursor-pointer"
                >
                  Edit
                </button>
                <button
                  v-if="u.uuid !== authStore.user?.uuid"
                  @click="confirmDelete(u)"
                  class="px-2.5 py-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition font-medium cursor-pointer"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <AppPagination
        :meta="paginationMeta"
        @changePage="handlePageChange"
        @changePerPage="handlePerPageChange"
      />
    </div>

    <!-- Create/Edit Modal -->
    <AppModal
      :isOpen="isModalOpen"
      :title="isEditing ? 'Edit User' : 'Create User'"
      @close="closeModal"
    >
      <form @submit.prevent="saveUser" class="space-y-4">
        <div>
          <label for="u_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Full Name *
          </label>
          <input
            id="u_name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
            placeholder="John Doe"
          />
        </div>

        <div>
          <label for="u_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Email Address *
          </label>
          <input
            id="u_email"
            v-model="form.email"
            type="email"
            required
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
            placeholder="user@example.com"
          />
        </div>

        <div>
          <label for="u_pass" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Password {{ isEditing ? '(Leave blank to keep unchanged)' : '*' }}
          </label>
          <input
            id="u_pass"
            v-model="form.password"
            type="password"
            :required="!isEditing"
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
            placeholder="••••••••"
          />
        </div>

        <div>
          <label for="u_role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Assigned Role *
          </label>
          <select
            id="u_role"
            v-model="form.role"
            required
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
          >
            <option value="user">Citizen User</option>
            <option value="admin">Administrator</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <input
            id="u_active"
            v-model="form.is_active"
            type="checkbox"
            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
          />
          <label for="u_active" class="text-xs text-slate-700 font-medium">User account is active</label>
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50 transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer"
          >
            {{ isSubmitting ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create User') }}
          </button>
        </div>
      </form>
    </AppModal>

    <!-- Delete Confirmation Modal -->
    <AppModal
      :isOpen="isDeleteModalOpen"
      title="Confirm Delete User"
      @close="isDeleteModalOpen = false"
    >
      <div class="space-y-4">
        <p class="text-xs text-slate-600">
          Are you sure you want to deactivate and delete user account
          <strong class="text-slate-900">{{ userToDelete?.name }}</strong> ({{ userToDelete?.email }})?
        </p>

        <div class="flex justify-end gap-2 pt-2">
          <button
            @click="isDeleteModalOpen = false"
            class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50 transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            @click="executeDelete"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer"
          >
            Delete
          </button>
        </div>
      </div>
    </AppModal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { userService } from '@/services/userService';
import { useAuthStore } from '@/stores/auth';
import type { UserProfile, PaginationMeta } from '@/types/api';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import AppModal from '@/components/ui/AppModal.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import dayjs from 'dayjs';

const authStore = useAuthStore();

const users = ref<UserProfile[]>([]);
const paginationMeta = ref<PaginationMeta | null>(null);
const isLoading = ref(false);
const isSubmitting = ref(false);
const feedbackMessage = ref('');
const errorMessage = ref('');

const searchQuery = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const currentPage = ref(1);
const perPage = ref(10);

const isModalOpen = ref(false);
const isEditing = ref(false);
const currentEditingUuid = ref<string | null>(null);

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'user',
  is_active: true,
});

const isDeleteModalOpen = ref(false);
const userToDelete = ref<UserProfile | null>(null);

const loadUsers = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const params: any = {
      page: currentPage.value,
      per_page: perPage.value,
    };
    if (searchQuery.value) params.search = searchQuery.value;
    if (roleFilter.value) params.role = roleFilter.value;
    if (statusFilter.value !== '') params.is_active = statusFilter.value === '1';

    const response = await userService.fetchUsers(params);
    users.value = response.data;
    if (response.meta?.pagination) {
      paginationMeta.value = response.meta.pagination;
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to load user list.';
  } finally {
    isLoading.value = false;
  }
};

let searchTimeout: any = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    loadUsers();
  }, 300);
};

const handlePageChange = (page: number) => {
  currentPage.value = page;
  loadUsers();
};

const handlePerPageChange = (size: number) => {
  perPage.value = size;
  currentPage.value = 1;
  loadUsers();
};

const openCreateModal = () => {
  isEditing.value = false;
  currentEditingUuid.value = null;
  form.value = {
    name: '',
    email: '',
    password: '',
    role: 'user',
    is_active: true,
  };
  isModalOpen.value = true;
};

const openEditModal = (u: UserProfile) => {
  isEditing.value = true;
  currentEditingUuid.value = u.uuid;
  form.value = {
    name: u.name,
    email: u.email,
    password: '',
    role: u.roles[0] || 'user',
    is_active: u.is_active,
  };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const saveUser = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';
  try {
    if (isEditing.value && currentEditingUuid.value) {
      await userService.updateUser(currentEditingUuid.value, form.value);
      feedbackMessage.value = 'User account updated successfully!';
    } else {
      await userService.createUser(form.value);
      feedbackMessage.value = 'User account created successfully!';
    }
    closeModal();
    loadUsers();
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to save user account.';
  } finally {
    isSubmitting.value = false;
  }
};

const toggleStatus = async (u: UserProfile) => {
  if (u.uuid === authStore.user?.uuid) return;
  try {
    await userService.toggleUserStatus(u.uuid, !u.is_active);
    u.is_active = !u.is_active;
    feedbackMessage.value = `User "${u.name}" is now ${u.is_active ? 'active' : 'inactive'}.`;
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to toggle user status.';
  }
};

const confirmDelete = (u: UserProfile) => {
  userToDelete.value = u;
  isDeleteModalOpen.value = true;
};

const executeDelete = async () => {
  if (!userToDelete.value) return;
  try {
    await userService.deleteUser(userToDelete.value.uuid);
    feedbackMessage.value = `User "${userToDelete.value.name}" deleted successfully.`;
    isDeleteModalOpen.value = false;
    userToDelete.value = null;
    loadUsers();
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Cannot delete user.';
    isDeleteModalOpen.value = false;
  }
};

const formatDate = (date?: string) => {
  if (!date) return '-';
  return dayjs(date).format('MMM D, YYYY');
};

onMounted(() => {
  loadUsers();
});
</script>

