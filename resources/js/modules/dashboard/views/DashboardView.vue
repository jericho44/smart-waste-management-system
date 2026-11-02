<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">System Dashboard</h1>
        <p class="text-xs text-slate-500 mt-1">Real-time overview of community reports and collection metrics</p>
      </div>

      <div class="flex items-center gap-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
          ● Live Status
        </span>
      </div>
    </div>

    <!-- Stat Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Reports</p>
        <p class="text-3xl font-extrabold text-slate-900 mt-2">1,248</p>
        <p class="text-xs text-emerald-600 mt-1 font-medium">↑ +12% from last month</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending</p>
        <p class="text-3xl font-extrabold text-amber-600 mt-2">42</p>
        <p class="text-xs text-slate-500 mt-1">Awaiting review</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">In Progress</p>
        <p class="text-3xl font-extrabold text-blue-600 mt-2">18</p>
        <p class="text-xs text-slate-500 mt-1">Assigned for pickup</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Completed</p>
        <p class="text-3xl font-extrabold text-emerald-600 mt-2">1,188</p>
        <p class="text-xs text-emerald-600 mt-1 font-medium">95.2% resolution rate</p>
      </div>
    </div>

    <!-- Chart Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Monthly Report Trends</h3>
        <div class="h-72">
          <apexchart
            type="area"
            height="100%"
            :options="trendChartOptions"
            :series="trendSeries"
          />
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div>
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Waste Categories</h3>
          <div class="h-60 flex items-center justify-center">
            <apexchart
              type="donut"
              width="100%"
              :options="categoryChartOptions"
              :series="categorySeries"
            />
          </div>
        </div>
        <p class="text-xs text-center text-slate-400 mt-4">Organic, Plastic, and Paper categories</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const trendSeries = ref([
  {
    name: 'Reports Submitted',
    data: [65, 80, 95, 120, 140, 190, 220],
  },
  {
    name: 'Resolved',
    data: [60, 75, 90, 110, 135, 180, 210],
  },
]);

const trendChartOptions = ref({
  chart: {
    toolbar: { show: false },
    fontFamily: 'inherit',
  },
  colors: ['#10b981', '#059669'],
  stroke: { curve: 'smooth', width: 2 },
  dataLabels: { enabled: false },
  xaxis: {
    categories: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
  },
  grid: {
    borderColor: '#f1f5f9',
  },
});

const categorySeries = ref([45, 35, 20]);
const categoryChartOptions = ref({
  labels: ['Organic Waste', 'Plastic Waste', 'Paper Waste'],
  colors: ['#10b981', '#3b82f6', '#f59e0b'],
  legend: { position: 'bottom' },
  dataLabels: { enabled: false },
});
</script>

