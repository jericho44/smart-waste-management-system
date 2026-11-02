<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
          {{ authStore.isAdmin ? 'Waste Reports Management' : 'My Waste Reports' }}
        </h1>
        <p class="text-xs text-slate-500 mt-1">
          {{ authStore.isAdmin ? 'Monitor, review, and dispatch waste collection operations' : 'Track and manage your submitted community waste reports' }}
        </p>
      </div>

      <router-link
        to="/reports/create"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-emerald-200 transition cursor-pointer self-start sm:self-auto"
      >
        <span>+ Submit New Report</span>
      </router-link>
    </div>

    <!-- Filters Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
      <!-- Search & Category -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <div class="relative md:col-span-2">
          <input
            v-model="filters.search"
            @input="handleSearchDebounce"
            type="text"
            placeholder="Search report title, description, or address..."
            class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
          />
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <div>
          <select
            v-model="filters.category_uuid"
            @change="loadReports"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.uuid" :value="cat.uuid">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <div>
          <select
            v-model="filters.status"
            @change="loadReports"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500"
          >
            <option value="">All Statuses</option>
            <option value="PENDING">Pending</option>
            <option value="IN_PROGRESS">In Progress</option>
            <option value="COMPLETED">Completed</option>
            <option value="REJECTED">Rejected</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Reports Grid / List -->
    <div v-if="isLoading" class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 text-xs text-slate-400 shadow-xs">
      Loading waste reports...
    </div>

    <div v-else-if="reports.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 space-y-3 shadow-xs">
      <div class="text-4xl">🗑️</div>
      <h3 class="text-sm font-bold text-slate-800">No waste reports found</h3>
      <p class="text-xs text-slate-500 max-w-sm mx-auto">
        There are currently no reports matching your filters. Submit a new report or adjust your search parameters.
      </p>
      <router-link
        to="/reports/create"
        class="inline-block px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition"
      >
        Submit a Report
      </router-link>
    </div>

    <div v-else class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="report in reports"
          :key="report.uuid"
          class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition overflow-hidden flex flex-col group"
        >
          <!-- Thumbnail Image -->
          <div class="h-44 bg-slate-100 relative overflow-hidden">
            <img
              v-if="report.images && report.images.length > 0"
              :src="report.images[0].url"
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              :alt="report.title"
            />
            <div
              v-else
              class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50 text-xs font-medium"
            >
              <span class="text-3xl mb-1">📷</span>
              <span>No Photo Attached</span>
            </div>

            <!-- Status Badge on Image -->
            <div class="absolute top-3 right-3 shadow-xs">
              <ReportStatusBadge :status="report.status" />
            </div>

            <!-- Category Pill -->
            <div class="absolute bottom-3 left-3">
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-900/70 text-white backdrop-blur-xs">
                {{ report.category?.name || 'Waste' }}
              </span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
            <div class="space-y-2">
              <h3 class="text-base font-bold text-slate-900 line-clamp-1 group-hover:text-emerald-600 transition">
                <router-link :to="`/reports/${report.uuid}`">
                  {{ report.title }}
                </router-link>
              </h3>
              <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                {{ report.description }}
              </p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
              <span v-if="authStore.isAdmin && report.user" class="font-medium text-slate-600">
                By: {{ report.user.name }}
              </span>
              <span v-else>
                {{ formatDate(report.created_at) }}
              </span>

              <router-link
                :to="`/reports/${report.uuid}`"
                class="text-emerald-600 font-bold hover:underline flex items-center gap-1"
              >
                <span>View Details</span>
                <span>&rarr;</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination Component -->
      <AppPagination
        :meta="paginationMeta"
        @changePage="handlePageChange"
        @changePerPage="handlePerPageChange"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { reportService } from '@/services/reportService';
import { categoryService } from '@/services/categoryService';
import { useAuthStore } from '@/stores/auth';
import type { WasteReport } from '@/types/report';
import type { WasteCategory } from '@/types/category';
import type { PaginationMeta } from '@/types/api';
import ReportStatusBadge from '@/components/ui/ReportStatusBadge.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import dayjs from 'dayjs';

const authStore = useAuthStore();

const reports = ref<WasteReport[]>([]);
const categories = ref<WasteCategory[]>([]);
const paginationMeta = ref<PaginationMeta | null>(null);
const isLoading = ref(false);

const filters = ref({
  search: '',
  category_uuid: '',
  status: '',
  page: 1,
  per_page: 9,
});

const loadCategories = async () => {
  try {
    const res = await categoryService.fetchCategories({ all: true });
    if ('data' in res) {
      categories.value = res.data as WasteCategory[];
    }
  } catch {
    // Silent catch
  }
};

const loadReports = async () => {
  isLoading.value = true;
  try {
    const params: any = {
      page: filters.value.page,
      per_page: filters.value.per_page,
    };
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.category_uuid) params.category_uuid = filters.value.category_uuid;
    if (filters.value.status) params.status = filters.value.status;

    const res = await reportService.fetchReports(params);
    reports.value = res.data;
    if (res.meta?.pagination) {
      paginationMeta.value = res.meta.pagination;
    }
  } catch {
    reports.value = [];
  } finally {
    isLoading.value = false;
  }
};

let searchTimeout: any = null;
const handleSearchDebounce = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    filters.value.page = 1;
    loadReports();
  }, 300);
};

const handlePageChange = (page: number) => {
  filters.value.page = page;
  loadReports();
};

const handlePerPageChange = (size: number) => {
  filters.value.per_page = size;
  filters.value.page = 1;
  loadReports();
};

const formatDate = (date?: string) => {
  if (!date) return '-';
  return dayjs(date).format('MMM D, YYYY');
};

onMounted(() => {
  loadCategories();
  loadReports();
});
</script>

