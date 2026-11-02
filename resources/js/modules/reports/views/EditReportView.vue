<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Waste Report</h1>
        <p class="text-xs text-slate-500 mt-1">Modify report information, category, or location</p>
      </div>

      <router-link
        :to="`/reports/${route.params.uuid}`"
        class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition"
      >
        &larr; Cancel & Return
      </router-link>
    </div>

    <!-- Error Banner -->
    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center justify-between">
      <span>{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="font-bold ml-2">&times;</button>
    </div>

    <form
      v-if="formLoaded"
      @submit.prevent="submitUpdate"
      class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-6"
    >
      <div>
        <label for="title" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
          Report Title *
        </label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          required
          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none"
        />
      </div>

      <div>
        <label for="category" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
          Waste Category *
        </label>
        <select
          id="category"
          v-model="form.category_uuid"
          required
          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none"
        >
          <option v-for="cat in categories" :key="cat.uuid" :value="cat.uuid">
            {{ cat.name }}
          </option>
        </select>
      </div>

      <div>
        <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
          Description *
        </label>
        <textarea
          id="description"
          v-model="form.description"
          rows="4"
          required
          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none"
        ></textarea>
      </div>

      <!-- Map Location Picker -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
          Pinpoint Location on Map *
        </label>
        <LocationPicker
          :lat="form.latitude"
          :lng="form.longitude"
          @update:lat="form.latitude = $event"
          @update:lng="form.longitude = $event"
        />
      </div>

      <div>
        <label for="address" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
          Street Address / Landmark (Optional)
        </label>
        <input
          id="address"
          v-model="form.address"
          type="text"
          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none"
        />
      </div>

      <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
        <router-link
          :to="`/reports/${route.params.uuid}`"
          class="px-5 py-2.5 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50 transition"
        >
          Cancel
        </router-link>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-200 transition cursor-pointer"
        >
          {{ isSubmitting ? 'Saving Changes...' : 'Save Changes' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { reportService } from '@/services/reportService';
import { categoryService } from '@/services/categoryService';
import type { WasteCategory } from '@/types/category';
import LocationPicker from '@/components/maps/LocationPicker.vue';

const route = useRoute();
const router = useRouter();

const categories = ref<WasteCategory[]>([]);
const isSubmitting = ref(false);
const formLoaded = ref(false);
const errorMessage = ref('');

const form = ref({
  title: '',
  category_uuid: '',
  description: '',
  latitude: -6.2088,
  longitude: 106.8456,
  address: '',
});

onMounted(async () => {
  try {
    const uuid = route.params.uuid as string;
    const [catRes, report] = await Promise.all([
      categoryService.fetchCategories({ all: true }),
      reportService.fetchReport(uuid),
    ]);

    if ('data' in catRes) {
      categories.value = catRes.data as WasteCategory[];
    }

    form.value = {
      title: report.title,
      category_uuid: report.category?.uuid || '',
      description: report.description,
      latitude: report.latitude,
      longitude: report.longitude,
      address: report.address || '',
    };
    formLoaded.value = true;
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to load report for editing.';
  }
});

const submitUpdate = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';
  try {
    const uuid = route.params.uuid as string;
    await reportService.updateReport(uuid, {
      title: form.value.title,
      description: form.value.description,
      category_uuid: form.value.category_uuid,
      latitude: form.value.latitude,
      longitude: form.value.longitude,
      address: form.value.address || null,
    });
    router.push(`/reports/${uuid}`);
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to update report.';
  } finally {
    isSubmitting.value = false;
  }
};
</script>

