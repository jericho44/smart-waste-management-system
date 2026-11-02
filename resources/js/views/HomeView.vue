<template>
  <div class="space-y-12">
    <!-- Hero Section -->
    <div class="bg-white rounded-3xl p-8 sm:p-12 shadow-sm border border-slate-200/80 text-center relative overflow-hidden">
      <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
      <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-emerald-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-6">
        ✨ Phase 1 Foundation Verified
      </span>

      <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight max-w-3xl mx-auto leading-tight">
        Smart Community <span class="text-emerald-600">Waste Management</span> Platform
      </h1>
      <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto">
        Empowering citizens and municipal administrators to monitor, classify, and resolve community waste reports efficiently in real-time.
      </p>

      <div class="mt-8 flex flex-wrap justify-center gap-4">
        <router-link
          to="/dashboard"
          class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-md shadow-emerald-200 transition"
        >
          View Dashboard
        </router-link>
        <router-link
          to="/login"
          class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition"
        >
          Sign In
        </router-link>
      </div>
    </div>

    <!-- Features / Foundation Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
          ⚡
        </div>
        <h3 class="text-lg font-bold text-slate-900">Modular Monolith</h3>
        <p class="text-sm text-slate-600 leading-relaxed">
          Clean architecture with decoupled Service Layers, DTOs, Form Requests, and API Resources powered by Laravel 12.
        </p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
          🗺️
        </div>
        <h3 class="text-lg font-bold text-slate-900">Geospatial Reports</h3>
        <p class="text-sm text-slate-600 leading-relaxed">
          Interactive map visualization with Leaflet and OpenStreetMap for accurate waste geolocation and reporting.
        </p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
          🛡️
        </div>
        <h3 class="text-lg font-bold text-slate-900">Role & Security</h3>
        <p class="text-sm text-slate-600 leading-relaxed">
          Granular RBAC with Spatie Permission, Sanctum token authentication, and standardized API envelope with Request IDs.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Home view
</script>

