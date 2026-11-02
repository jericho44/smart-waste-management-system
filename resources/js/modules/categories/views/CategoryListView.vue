<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Waste Categories</h1>
        <p class="text-xs text-slate-500 mt-1">Manage waste classification types for community reporting</p>
      </div>

      <button
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-emerald-200 transition cursor-pointer"
      >
        <span>+ Add Category</span>
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
          placeholder="Search name, slug, or description..."
          class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white"
        />
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <select
          v-model="statusFilter"
          @change="loadCategories"
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
              <th class="py-3.5 px-4">Category Name</th>
              <th class="py-3.5 px-4">Slug</th>
              <th class="py-3.5 px-4">Reports</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4">Created Date</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="isLoading" class="text-center">
              <td colspan="6" class="py-12 text-slate-400">Loading categories...</td>
            </tr>
            <tr v-else-if="categories.length === 0" class="text-center">
              <td colspan="6" class="py-12 text-slate-400">No categories found matching criteria.</td>
            </tr>
            <tr
              v-else
              v-for="cat in categories"
              :key="cat.uuid"
              class="hover:bg-slate-50/70 transition"
            >
              <td class="py-3.5 px-4 font-semibold text-slate-900">
                <div>{{ cat.name }}</div>
                <div v-if="cat.description" class="text-slate-400 font-normal line-clamp-1 max-w-xs mt-0.5">
                  {{ cat.description }}
                </div>
              </td>
              <td class="py-3.5 px-4 font-mono text-slate-500">
                {{ cat.slug }}
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium">
                  {{ cat.reports_count || 0 }} reports
                </span>
              </td>
              <td class="py-3.5 px-4">
                <button
                  @click="toggleStatus(cat)"
                  class="cursor-pointer"
                  title="Click to toggle status"
                >
                  <StatusBadge :active="cat.is_active" />
                </button>
              </td>
              <td class="py-3.5 px-4 text-slate-500">
                {{ formatDate(cat.created_at) }}
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <button
                  @click="openEditModal(cat)"
                  class="px-2.5 py-1 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition font-medium cursor-pointer"
                >
                  Edit
                </button>
                <button
                  @click="confirmDelete(cat)"
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
      :title="isEditing ? 'Edit Category' : 'Create Category'"
      @close="closeModal"
    >
      <form @submit.prevent="saveCategory" class="space-y-4">
        <div>
          <label for="cat_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Category Name *
          </label>
          <input
            id="cat_name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
            placeholder="e.g. Hazardous Materials"
          />
        </div>

        <div>
          <label for="cat_slug" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Slug (Optional)
          </label>
          <input
            id="cat_slug"
            v-model="form.slug"
            type="text"
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none font-mono"
            placeholder="Auto-generated if left blank"
          />
        </div>

        <div>
          <label for="cat_desc" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Description
          </label>
          <textarea
            id="cat_desc"
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
            placeholder="Brief explanation of waste types falling into this category..."
          ></textarea>
        </div>

        <div class="flex items-center gap-2">
          <input
            id="cat_active"
            v-model="form.is_active"
            type="checkbox"
            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
          />
          <label for="cat_active" class="text-xs text-slate-700 font-medium">Category is active for reporting</label>
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
            {{ isSubmitting ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create Category') }}
          </button>
        </div>
      </form>
    </AppModal>

    <!-- Delete Confirmation Modal -->
    <AppModal
      :isOpen="isDeleteModalOpen"
      title="Confirm Delete Category"
      @close="isDeleteModalOpen = false"
    >
      <div class="space-y-4">
        <p class="text-xs text-slate-600">
          Are you sure you want to delete category
          <strong class="text-slate-900">{{ categoryToDelete?.name }}</strong>?
          This action cannot be performed if reports are currently attached to it.
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
import { categoryService } from '@/services/categoryService';
import type { WasteCategory } from '@/types/category';
import type { PaginationMeta } from '@/types/api';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import AppModal from '@/components/ui/AppModal.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import dayjs from 'dayjs';

const categories = ref<WasteCategory[]>([]);
const paginationMeta = ref<PaginationMeta | null>(null);
const isLoading = ref(false);
const isSubmitting = ref(false);
const feedbackMessage = ref('');
const errorMessage = ref('');

const searchQuery = ref('');
const statusFilter = ref('');
const currentPage = ref(1);
const perPage = ref(10);

const isModalOpen = ref(false);
const isEditing = ref(false);
const currentEditingUuid = ref<string | null>(null);

const form = ref({
  name: '',
  slug: '',
  description: '',
  is_active: true,
});

const isDeleteModalOpen = ref(false);
const categoryToDelete = ref<WasteCategory | null>(null);

const loadCategories = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const params: any = {
      page: currentPage.value,
      per_page: perPage.value,
    };
    if (searchQuery.value) params.search = searchQuery.value;
    if (statusFilter.value !== '') params.is_active = statusFilter.value === '1';

    const response = await categoryService.fetchCategories(params);
    if ('data' in response) {
      categories.value = response.data as WasteCategory[];
      if ('pagination' in (response.meta || {})) {
        paginationMeta.value = (response.meta as any).pagination;
      }
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to load waste categories.';
  } finally {
    isLoading.value = false;
  }
};

let searchTimeout: any = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    loadCategories();
  }, 300);
};

const handlePageChange = (page: number) => {
  currentPage.value = page;
  loadCategories();
};

const handlePerPageChange = (size: number) => {
  perPage.value = size;
  currentPage.value = 1;
  loadCategories();
};

const openCreateModal = () => {
  isEditing.value = false;
  currentEditingUuid.value = null;
  form.value = {
    name: '',
    slug: '',
    description: '',
    is_active: true,
  };
  isModalOpen.value = true;
};

const openEditModal = (cat: WasteCategory) => {
  isEditing.value = true;
  currentEditingUuid.value = cat.uuid;
  form.value = {
    name: cat.name,
    slug: cat.slug,
    description: cat.description || '',
    is_active: cat.is_active,
  };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const saveCategory = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';
  try {
    if (isEditing.value && currentEditingUuid.value) {
      await categoryService.updateCategory(currentEditingUuid.value, form.value);
      feedbackMessage.value = 'Category updated successfully!';
    } else {
      await categoryService.createCategory(form.value);
      feedbackMessage.value = 'Category created successfully!';
    }
    closeModal();
    loadCategories();
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to save category.';
  } finally {
    isSubmitting.value = false;
  }
};

const toggleStatus = async (cat: WasteCategory) => {
  try {
    await categoryService.toggleCategoryStatus(cat.uuid, !cat.is_active);
    cat.is_active = !cat.is_active;
    feedbackMessage.value = `Category "${cat.name}" is now ${cat.is_active ? 'active' : 'inactive'}.`;
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to toggle category status.';
  }
};

const confirmDelete = (cat: WasteCategory) => {
  categoryToDelete.value = cat;
  isDeleteModalOpen.value = true;
};

const executeDelete = async () => {
  if (!categoryToDelete.value) return;
  try {
    await categoryService.deleteCategory(categoryToDelete.value.uuid);
    feedbackMessage.value = `Category "${categoryToDelete.value.name}" deleted successfully.`;
    isDeleteModalOpen.value = false;
    categoryToDelete.value = null;
    loadCategories();
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || err.response?.data?.errors?.category?.[0] || 'Cannot delete category.';
    isDeleteModalOpen.value = false;
  }
};

const formatDate = (date?: string) => {
  if (!date) return '-';
  return dayjs(date).format('MMM D, YYYY');
};

onMounted(() => {
  loadCategories();
});
</script>

