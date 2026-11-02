<template>
  <div v-if="meta" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-3 px-4 bg-white border-t border-slate-200/80 rounded-b-2xl text-xs text-slate-600">
    <!-- Info & Per Page selector -->
    <div class="flex items-center gap-3">
      <span>
        Showing <span class="font-semibold text-slate-900">{{ meta.from || 0 }}</span> to
        <span class="font-semibold text-slate-900">{{ meta.to || 0 }}</span> of
        <span class="font-semibold text-slate-900">{{ meta.total }}</span> results
      </span>

      <div class="flex items-center gap-1.5 ml-2">
        <label for="per_page_select" class="text-slate-400">Per page:</label>
        <select
          id="per_page_select"
          :value="meta.per_page"
          @change="$emit('changePerPage', Number(($event.target as HTMLSelectElement).value))"
          class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>

    <!-- Navigation buttons -->
    <div class="flex items-center space-x-1">
      <button
        :disabled="meta.current_page <= 1"
        @click="$emit('changePage', 1)"
        class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium"
        title="First Page"
      >
        &laquo;
      </button>

      <button
        :disabled="!meta.has_previous"
        @click="$emit('changePage', meta.current_page - 1)"
        class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium"
      >
        Prev
      </button>

      <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 font-bold rounded-lg border border-emerald-200">
        {{ meta.current_page }} / {{ meta.last_page || 1 }}
      </span>

      <button
        :disabled="!meta.has_next"
        @click="$emit('changePage', meta.current_page + 1)"
        class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium"
      >
        Next
      </button>

      <button
        :disabled="meta.current_page >= meta.last_page"
        @click="$emit('changePage', meta.last_page)"
        class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium"
        title="Last Page"
      >
        &raquo;
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PaginationMeta } from '@/types/api';

defineProps<{
  meta?: PaginationMeta | null;
}>();

defineEmits<{
  (e: 'changePage', page: number): void;
  (e: 'changePerPage', perPage: number): void;
}>();
</script>

