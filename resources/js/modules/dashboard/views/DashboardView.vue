<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-2xl font-black text-slate-900 tracking-tight">
            {{ authStore.isAdmin ? 'System Dashboard' : 'Citizen Dashboard' }}
          </h1>
          <span
            v-if="authStore.isAdmin"
            class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200"
          >
            Admin Overview
          </span>
          <span
            v-else
            class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200"
          >
            Community Contributor
          </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
          {{
            authStore.isAdmin
              ? 'Real-time overview of community reports, resolution rates, and categories'
              : 'Track your submitted waste reports, progress updates, and environmental impact'
          }}
        </p>
      </div>

      <div class="flex items-center gap-2.5 self-start sm:self-auto">
        <button
          @click="() => refetch()"
          :disabled="isFetching"
          class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer disabled:opacity-50"
          title="Refresh dashboard data"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-3.5 w-3.5"
            :class="{ 'animate-spin': isFetching }"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Refresh</span>
        </button>

        <router-link
          to="/reports/create"
          class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-emerald-200 transition cursor-pointer"
        >
          <span>+ Submit Report</span>
        </router-link>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="i in 4" :key="i" class="h-28 bg-white rounded-2xl border border-slate-200/80 animate-pulse p-5">
          <div class="h-4 bg-slate-100 rounded w-24 mb-3"></div>
          <div class="h-8 bg-slate-200 rounded w-16"></div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 h-80 bg-white rounded-2xl border border-slate-200/80 animate-pulse p-6"></div>
        <div class="h-80 bg-white rounded-2xl border border-slate-200/80 animate-pulse p-6"></div>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="isError"
      class="p-6 bg-red-50 border border-red-200 rounded-2xl text-center space-y-3"
    >
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100 text-red-600 mb-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <p class="text-sm font-bold text-red-800">Failed to load dashboard metrics</p>
      <p class="text-xs text-red-600 max-w-md mx-auto">
        An error occurred while fetching real-time statistics from the server.
      </p>
      <button
        @click="() => refetch()"
        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer"
      >
        Try Again
      </button>
    </div>

    <!-- Loaded Dashboard Content -->
    <div v-else-if="dashboardData" class="space-y-6">
      <!-- Stat Metric Cards -->
      <div
        :class="[
          'grid gap-4',
          authStore.isAdmin
            ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'
            : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-5'
        ]"
      >
        <!-- Card 1: Total Reports -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              {{ authStore.isAdmin ? 'Total Reports' : 'My Reports' }}
            </p>
            <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </span>
          </div>
          <p class="text-2xl font-black text-slate-900 mt-2">{{ dashboardData.total_reports }}</p>
          <p class="text-[11px] text-slate-400 mt-1">All-time submissions</p>
        </div>

        <!-- Card 2: Pending Reports -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-amber-200 transition">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pending</p>
            <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </span>
          </div>
          <p class="text-2xl font-black text-amber-600 mt-2">{{ dashboardData.pending_reports }}</p>
          <p class="text-[11px] text-slate-400 mt-1">Awaiting dispatch</p>
        </div>

        <!-- Card 3: In Progress Reports -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-blue-200 transition">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">In Progress</p>
            <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </span>
          </div>
          <p class="text-2xl font-black text-blue-600 mt-2">{{ dashboardData.in_progress_reports }}</p>
          <p class="text-[11px] text-slate-400 mt-1">Cleanup underway</p>
        </div>

        <!-- Card 4: Completed Reports -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-200 transition">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Completed</p>
            <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </span>
          </div>
          <p class="text-2xl font-black text-emerald-600 mt-2">{{ dashboardData.completed_reports }}</p>
          <p class="text-[11px] text-slate-400 mt-1">Resolved & cleared</p>
        </div>

        <!-- Card 5: Rejected Reports -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-red-200 transition">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rejected</p>
            <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </span>
          </div>
          <p class="text-2xl font-black text-rose-600 mt-2">{{ dashboardData.rejected_reports }}</p>
          <p class="text-[11px] text-slate-400 mt-1">Invalid or duplicate</p>
        </div>

        <!-- Card 6: Total Users (Admin Only) -->
        <div
          v-if="authStore.isAdmin && 'total_users' in dashboardData"
          class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-violet-200 transition"
        >
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Citizens</p>
            <span class="p-1.5 rounded-lg bg-violet-50 text-violet-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </span>
          </div>
          <p class="text-2xl font-black text-violet-600 mt-2">{{ dashboardData.total_users }}</p>
          <p class="text-[11px] text-slate-400 mt-1">Registered accounts</p>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Trends Area Chart -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                  {{ authStore.isAdmin ? 'Monthly Report Trends' : 'My Submission Activity' }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Report submissions vs resolutions over the last 6 months</p>
              </div>
              <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">
                Last 6 Months
              </span>
            </div>

            <div class="h-72">
              <apexchart
                type="area"
                height="100%"
                :options="trendChartOptions"
                :series="trendSeries"
              />
            </div>
          </div>
        </div>

        <!-- Categories Donut Chart -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Waste Categories</h3>
                <p class="text-xs text-slate-500 mt-0.5">Distribution by waste classification</p>
              </div>
            </div>

            <div v-if="hasCategoryData" class="h-64 flex items-center justify-center">
              <apexchart
                type="donut"
                width="100%"
                height="100%"
                :options="categoryChartOptions"
                :series="categorySeries"
              />
            </div>

            <div v-else class="h-64 flex flex-col items-center justify-center text-center p-4">
              <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                🏷️
              </div>
              <p class="text-xs font-bold text-slate-700">No category data yet</p>
              <p class="text-[11px] text-slate-400 mt-1">Reports will be charted here once submitted.</p>
            </div>
          </div>

          <!-- Category Legend Percentages -->
          <div v-if="hasCategoryData" class="mt-4 pt-4 border-t border-slate-100 space-y-2">
            <div
              v-for="cat in dashboardData.reports_by_category"
              :key="cat.uuid"
              class="flex items-center justify-between text-xs"
            >
              <span class="font-medium text-slate-600 truncate max-w-[140px]">{{ cat.name }}</span>
              <div class="flex items-center gap-2">
                <span class="font-bold text-slate-900">{{ cat.count }}</span>
                <span class="text-slate-400 text-[11px]">({{ cat.percentage }}%)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Reports Table / List -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
              {{ authStore.isAdmin ? 'Recent Community Reports' : 'My Recent Submissions' }}
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              {{ authStore.isAdmin ? 'Latest submitted reports awaiting or undergoing collection' : 'Your latest reported incidents' }}
            </p>
          </div>

          <router-link
            to="/reports"
            class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition"
          >
            <span>View All</span>
            <span>&rarr;</span>
          </router-link>
        </div>

        <!-- If reports exist -->
        <div v-if="dashboardData.latest_reports && dashboardData.latest_reports.length > 0" class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
              <tr>
                <th class="py-3 px-6">Report Title & Location</th>
                <th class="py-3 px-4">Category</th>
                <th v-if="authStore.isAdmin" class="py-3 px-4">Reported By</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-6 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="report in dashboardData.latest_reports"
                :key="report.uuid"
                class="hover:bg-slate-50/60 transition"
              >
                <!-- Title & Location -->
                <td class="py-3.5 px-6">
                  <div class="font-bold text-slate-900 text-xs line-clamp-1">{{ report.title }}</div>
                  <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="truncate max-w-xs">{{ report.address || `${report.latitude.toFixed(4)}, ${report.longitude.toFixed(4)}` }}</span>
                  </div>
                </td>

                <!-- Category -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span
                    v-if="report.category"
                    class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700"
                  >
                    {{ report.category.name }}
                  </span>
                  <span v-else class="text-slate-400 italic">None</span>
                </td>

                <!-- Reported By (Admin only) -->
                <td v-if="authStore.isAdmin" class="py-3.5 px-4 whitespace-nowrap">
                  <div v-if="report.user" class="font-medium text-slate-800">{{ report.user.name }}</div>
                  <div v-if="report.user" class="text-[11px] text-slate-400">{{ report.user.email }}</div>
                  <span v-else class="text-slate-400 italic">Anonymous</span>
                </td>

                <!-- Status Badge -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <ReportStatusBadge :status="report.status" />
                </td>

                <!-- Date -->
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                  {{ formatDate(report.created_at) }}
                </td>

                <!-- Action -->
                <td class="py-3.5 px-6 text-right whitespace-nowrap">
                  <router-link
                    :to="`/reports/${report.uuid}`"
                    class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline"
                  >
                    <span>Details</span>
                    <span>&rarr;</span>
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty state -->
        <div v-else class="p-8 text-center space-y-3">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 text-slate-400 mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p class="text-sm font-bold text-slate-700">No reports recorded yet</p>
          <p class="text-xs text-slate-400 max-w-sm mx-auto">
            {{ authStore.isAdmin ? 'Community reports will automatically appear here as citizens submit them.' : 'You have not submitted any waste reports yet. Join the community cleanup!' }}
          </p>
          <router-link
            to="/reports/create"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition"
          >
            <span>+ Submit First Report</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useQuery } from '@tanstack/vue-query';
import { useAuthStore } from '@/stores/auth';
import { dashboardService } from '@/services/dashboardService';
import type { AdminDashboardData, UserDashboardData } from '@/types/dashboard';
import ReportStatusBadge from '@/components/ui/ReportStatusBadge.vue';
import dayjs from 'dayjs';

const authStore = useAuthStore();

// TanStack Query for dynamic caching, automatic refetch, and background refresh
const {
  data: dashboardData,
  isLoading,
  isError,
  refetch,
  isFetching,
} = useQuery<AdminDashboardData | UserDashboardData>({
  queryKey: computed(() => ['dashboard', authStore.isAdmin ? 'admin' : 'user']),
  queryFn: () => {
    return authStore.isAdmin
      ? dashboardService.fetchAdminDashboard()
      : dashboardService.fetchUserDashboard();
  },
});

function formatDate(dateString: string): string {
  return dayjs(dateString).format('MMM D, YYYY HH:mm');
}

// Check if category data exists
const hasCategoryData = computed(() => {
  return (
    dashboardData.value?.reports_by_category &&
    dashboardData.value.reports_by_category.some((c) => c.count > 0)
  );
});

// Trend Chart (ApexCharts area)
const trendSeries = computed(() => {
  if (!dashboardData.value?.monthly_statistics) return [];
  return [
    {
      name: 'Submitted Reports',
      data: dashboardData.value.monthly_statistics.map((m) => m.submitted),
    },
    {
      name: 'Resolved / Completed',
      data: dashboardData.value.monthly_statistics.map((m) => m.completed),
    },
  ];
});

const trendChartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    fontFamily: 'inherit',
  },
  colors: ['#059669', '#3b82f6'],
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [20, 100],
    },
  },
  dataLabels: { enabled: false },
  xaxis: {
    categories: dashboardData.value?.monthly_statistics?.map((m) => m.short_label) ?? [],
    labels: {
      style: {
        fontSize: '11px',
        fontWeight: 500,
        colors: '#64748b',
      },
    },
  },
  yaxis: {
    labels: {
      formatter: (val: number) => Math.round(val).toString(),
      style: {
        fontSize: '11px',
        fontWeight: 500,
        colors: '#64748b',
      },
    },
    min: 0,
    forceNiceScale: true,
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 3,
  },
  tooltip: {
    theme: 'light',
  },
  legend: {
    position: 'top',
    horizontalAlign: 'right',
    fontSize: '12px',
    markers: {
      radius: 12,
    },
  },
}));

// Category Donut Chart
const categorySeries = computed(() => {
  if (!dashboardData.value?.reports_by_category) return [];
  return dashboardData.value.reports_by_category.map((c) => c.count);
});

const categoryChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    fontFamily: 'inherit',
  },
  labels: dashboardData.value?.reports_by_category?.map((c) => c.name) ?? [],
  colors: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'],
  legend: {
    position: 'bottom',
    horizontalAlign: 'center',
    fontSize: '11px',
    markers: {
      radius: 12,
    },
  },
  dataLabels: {
    enabled: true,
    formatter: (val: number) => `${Math.round(val)}%`,
    style: {
      fontSize: '11px',
      fontWeight: 'bold',
    },
  },
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
        labels: {
          show: true,
          name: {
            fontSize: '12px',
            color: '#64748b',
          },
          value: {
            fontSize: '20px',
            fontWeight: 800,
            color: '#0f172a',
            formatter: (val: string) => val,
          },
          total: {
            show: true,
            label: 'Total Reports',
            fontSize: '11px',
            fontWeight: 600,
            color: '#64748b',
            formatter: () => dashboardData.value?.total_reports?.toString() ?? '0',
          },
        },
      },
    },
  },
}));
</script>
