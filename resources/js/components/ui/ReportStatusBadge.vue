<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold tracking-wide border',
      statusClasses.badge
    ]"
  >
    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="statusClasses.dot"></span>
    {{ formattedStatus }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { ReportStatusType } from '@/types/report';

const props = defineProps<{
  status: ReportStatusType | string;
}>();

const formattedStatus = computed(() => {
  switch (props.status) {
    case 'PENDING':
      return 'Pending';
    case 'IN_PROGRESS':
      return 'In Progress';
    case 'COMPLETED':
      return 'Completed';
    case 'REJECTED':
      return 'Rejected';
    default:
      return props.status;
  }
});

const statusClasses = computed(() => {
  switch (props.status) {
    case 'PENDING':
      return {
        badge: 'bg-amber-50 text-amber-700 border-amber-200',
        dot: 'bg-amber-500',
      };
    case 'IN_PROGRESS':
      return {
        badge: 'bg-blue-50 text-blue-700 border-blue-200',
        dot: 'bg-blue-500',
      };
    case 'COMPLETED':
      return {
        badge: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        dot: 'bg-emerald-500',
      };
    case 'REJECTED':
      return {
        badge: 'bg-rose-50 text-rose-700 border-rose-200',
        dot: 'bg-rose-500',
      };
    default:
      return {
        badge: 'bg-slate-100 text-slate-700 border-slate-200',
        dot: 'bg-slate-400',
      };
  }
});
</script>

