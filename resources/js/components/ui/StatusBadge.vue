<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold tracking-wide',
      activeClasses
    ]"
  >
    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="dotClasses"></span>
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  active: boolean;
  activeText?: string;
  inactiveText?: string;
}>();

const label = computed(() => {
  if (props.active) {
    return props.activeText || 'Active';
  }
  return props.inactiveText || 'Inactive';
});

const activeClasses = computed(() => {
  return props.active
    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
    : 'bg-slate-100 text-slate-600 border border-slate-200';
});

const dotClasses = computed(() => {
  return props.active ? 'bg-emerald-500' : 'bg-slate-400';
});
</script>

