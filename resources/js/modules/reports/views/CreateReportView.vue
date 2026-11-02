<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Submit Waste Report</h1>
        <p class="text-xs text-slate-500 mt-1">Report community waste or illegal dumping sites for municipal action</p>
      </div>

      <router-link
        to="/reports"
        class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition"
      >
        &larr; Back to Reports
      </router-link>
    </div>

    <!-- Error Banner -->
    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center justify-between">
      <span>{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="font-bold ml-2">&times;</button>
    </div>

    <!-- Submission Form -->
    <form @submit.prevent="submitReport" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-6">
      <!-- Title -->
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
          placeholder="e.g. Uncollected plastic waste accumulating on riverside"
        />
        <p v-if="errors.title" class="text-xs text-red-600 mt-1">{{ errors.title[0] }}</p>
      </div>

      <!-- Category Selection -->
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
          <option value="" disabled>Select waste category</option>
          <option v-for="cat in categories" :key="cat.uuid" :value="cat.uuid">
            {{ cat.name }}
          </option>
        </select>
        <p v-if="errors.category_uuid" class="text-xs text-red-600 mt-1">{{ errors.category_uuid[0] }}</p>
      </div>

      <!-- Description -->
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
          placeholder="Provide detailed description of the waste type, approximate volume, hazards, or landmarks..."
        ></textarea>
        <p v-if="errors.description" class="text-xs text-red-600 mt-1">{{ errors.description[0] }}</p>
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

      <!-- Street Address (Optional) -->
      <div>
        <label for="address" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
          Street Address / Landmark (Optional)
        </label>
        <input
          id="address"
          v-model="form.address"
          type="text"
          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm transition focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none"
          placeholder="e.g. In front of Community Center Block B"
        />
      </div>

      <!-- Photo Upload -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
          Upload Waste Photos (Optional, Up to 5)
        </label>
        <ImageUpload
          :maxFiles="5"
          @update:files="uploadedFiles = $event"
        />
        <p v-if="errors.images" class="text-xs text-red-600 mt-1">{{ errors.images[0] }}</p>
      </div>

      <!-- Submit Buttons -->
      <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
        <router-link
          to="/reports"
          class="px-5 py-2.5 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50 transition"
        >
          Cancel
        </router-link>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-200 transition cursor-pointer flex items-center gap-2"
        >
          <span v-if="isSubmitting" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full"></span>
          <span>{{ isSubmitting ? 'Submitting Report...' : 'Submit Report' }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { reportService } from '@/services/reportService';
import { categoryService } from '@/services/categoryService';
import type { WasteCategory } from '@/types/category';
import LocationPicker from '@/components/maps/LocationPicker.vue';
import ImageUpload from '@/components/ui/ImageUpload.vue';

const router = useRouter();

const categories = ref<WasteCategory[]>([]);
const isSubmitting = ref(false);
const errorMessage = ref('');
const errors = ref<Record<string, string[]>>({});
const uploadedFiles = ref<File[]>([]);

const form = ref({
  title: '',
  category_uuid: '',
  description: '',
  latitude: -6.2088,
  longitude: 106.8456,
  address: '',
});

const loadCategories = async () => {
  try {
    const res = await categoryService.fetchCategories({ all: true, is_active: true });
    if ('data' in res) {
      categories.value = res.data as WasteCategory[];
      if (categories.value.length > 0) {
        form.value.category_uuid = categories.value[0].uuid;
      }
    }
  } catch {
    //
  }
};

const submitReport = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    const newReport = await reportService.createReport({
      title: form.value.title,
      description: form.value.description,
      category_uuid: form.value.category_uuid,
      latitude: form.value.latitude,
      longitude: form.value.longitude,
      address: form.value.address || null,
      images: uploadedFiles.value,
    });

    router.push(`/reports/${newReport.uuid}`);
  } catch (err: any) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors;
    } else {
      errorMessage.value = err.response?.data?.message || 'Failed to submit report. Please check the form.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  loadCategories();
});
</script>

