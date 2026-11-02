<template>
  <div class="min-h-screen bg-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <div class="inline-flex h-12 w-12 rounded-2xl bg-emerald-600 text-white items-center justify-center font-bold text-2xl shadow-lg shadow-emerald-200 mb-4">
        🌱
      </div>
      <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">EcoSmart Waste</h2>
      <p class="mt-1 text-sm text-slate-600">Smart Community Waste Management System</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-6 shadow-sm border border-slate-200 rounded-2xl sm:px-10">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Auth layout setup
</script>

