<template>
  <div class="space-y-6 max-w-5xl mx-auto">
    <!-- Back & Breadcrumb -->
    <div class="flex items-center justify-between">
      <router-link
        to="/reports"
        class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5"
      >
        <span>&larr;</span>
        <span>Back to Reports</span>
      </router-link>

      <div class="flex items-center gap-2">
        <!-- Citizen Edit/Delete if Pending -->
        <template v-if="canEditOrDelete">
          <router-link
            :to="`/reports/${report?.uuid}/edit`"
            class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition shadow-xs"
          >
            Edit Report
          </router-link>
          <button
            @click="isDeleteModalOpen = true"
            class="px-3.5 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl transition cursor-pointer"
          >
            Delete
          </button>
        </template>

        <!-- Admin Transition Status Button -->
        <button
          v-if="authStore.isAdmin && canTransition"
          @click="openTransitionModal"
          class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm shadow-emerald-200 cursor-pointer"
        >
          Update Status
        </button>
      </div>
    </div>

    <!-- Feedback Banner -->
    <div v-if="feedbackMessage" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center justify-between">
      <span>{{ feedbackMessage }}</span>
      <button @click="feedbackMessage = ''" class="font-bold ml-2">&times;</button>
    </div>

    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center justify-between">
      <span>{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="font-bold ml-2">&times;</button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="bg-white rounded-3xl p-16 text-center text-slate-400 text-xs border border-slate-200 shadow-xs">
      Loading report details...
    </div>

    <!-- Main Content Grid -->
    <div v-else-if="report" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column (Overview, Photos) -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Report Overview Card -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
              {{ report.category?.name || 'General Waste' }}
            </span>
            <ReportStatusBadge :status="report.status" />
          </div>

          <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-snug">
            {{ report.title }}
          </h1>

          <p class="text-sm text-slate-600 whitespace-pre-line leading-relaxed">
            {{ report.description }}
          </p>

          <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-400">
            <div>
              Reported by:
              <strong class="text-slate-800 font-semibold">{{ report.user?.name || 'Citizen' }}</strong>
            </div>
            <div>
              Submitted on:
              <span class="text-slate-600">{{ formatDate(report.created_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Photos Gallery -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
              Report Photos ({{ report.images?.length || 0 }})
            </h3>
          </div>

          <div v-if="report.images && report.images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div
              v-for="img in report.images"
              :key="img.uuid"
              class="relative rounded-2xl overflow-hidden aspect-square border border-slate-200 bg-slate-50 group shadow-xs"
            >
              <img
                :src="img.url"
                class="w-full h-full object-cover cursor-pointer hover:scale-105 transition duration-300"
                @click="openLightbox(img.url)"
                alt="Report photo"
              />

              <button
                v-if="canEditOrDelete"
                @click="deleteImage(img.uuid)"
                class="absolute top-1.5 right-1.5 h-6 w-6 rounded-full bg-slate-900/70 hover:bg-red-600 text-white flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition cursor-pointer"
                title="Delete photo"
              >
                &times;
              </button>
            </div>
          </div>
          <div v-else class="text-xs text-slate-400 py-6 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
            No photos uploaded for this report.
          </div>
        </div>
      </div>

      <!-- Right Column (Location, Timeline) -->
      <div class="space-y-6">
        <!-- Location Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Report Location</h3>

          <div v-if="report.address" class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">
            📍 <span class="font-medium">{{ report.address }}</span>
          </div>

          <LocationViewer
            :lat="report.latitude"
            :lng="report.longitude"
          />
        </div>

        <!-- Status History Audit Timeline -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Status History Timeline</h3>

          <div class="space-y-4 relative pl-4 before:absolute before:left-1 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
            <div
              v-for="item in report.status_histories"
              :key="item.uuid"
              class="relative space-y-1"
            >
              <div class="absolute -left-4 top-1 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-4 ring-white"></div>
              <div class="flex items-center gap-2">
                <ReportStatusBadge :status="item.new_status" />
                <span class="text-[11px] text-slate-400 font-mono">{{ formatDateShort(item.created_at) }}</span>
              </div>
              <p v-if="item.notes" class="text-xs text-slate-600 italic bg-slate-50 p-2 rounded-lg border border-slate-100">
                "{{ item.notes }}"
              </p>
              <p class="text-[10px] text-slate-400">
                Action by: {{ item.changed_by?.name || 'System' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Admin Status Transition Modal -->
    <AppModal
      :isOpen="isTransitionModalOpen"
      title="Update Waste Report Status"
      @close="isTransitionModalOpen = false"
    >
      <form @submit.prevent="executeTransition" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Current Status
          </label>
          <div class="p-2.5 bg-slate-100 text-slate-800 rounded-xl text-xs font-bold">
            {{ report?.status }}
          </div>
        </div>

        <div>
          <label for="next_status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            New Status *
          </label>
          <select
            id="next_status"
            v-model="transitionForm.status"
            required
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
          >
            <option v-for="opt in availableNextStatuses" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>

        <div>
          <label for="status_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Action Notes & Observations
          </label>
          <textarea
            id="status_notes"
            v-model="transitionForm.notes"
            rows="3"
            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white focus:outline-none"
            placeholder="e.g. Dispatched truck #3 for site cleanup..."
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            @click="isTransitionModalOpen = false"
            class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50 transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isTransitioning"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs transition"
          >
            {{ isTransitioning ? 'Updating...' : 'Confirm Status Change' }}
          </button>
        </div>
      </form>
    </AppModal>

    <!-- Delete Confirmation Modal -->
    <AppModal
      :isOpen="isDeleteModalOpen"
      title="Confirm Delete Report"
      @close="isDeleteModalOpen = false"
    >
      <div class="space-y-4">
        <p class="text-xs text-slate-600 leading-relaxed">
          Are you sure you want to delete report <strong>"{{ report?.title }}"</strong>? This action will remove the report and all attached photos.
        </p>

        <div class="flex justify-end gap-2 pt-2">
          <button
            @click="isDeleteModalOpen = false"
            class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50 transition"
          >
            Cancel
          </button>
          <button
            @click="executeDelete"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-xs transition"
          >
            Delete
          </button>
        </div>
      </div>
    </AppModal>

    <!-- Lightbox Modal -->
    <Teleport to="body">
      <div
        v-if="lightboxUrl"
        class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 backdrop-blur-xs cursor-pointer"
        @click="lightboxUrl = null"
      >
        <img :src="lightboxUrl" class="max-w-4xl max-h-[90vh] object-contain rounded-2xl shadow-2xl" alt="Enlarged photo" />
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { reportService } from '@/services/reportService';
import { useAuthStore } from '@/stores/auth';
import type { WasteReport, ReportStatusType } from '@/types/report';
import ReportStatusBadge from '@/components/ui/ReportStatusBadge.vue';
import LocationViewer from '@/components/maps/LocationViewer.vue';
import AppModal from '@/components/ui/AppModal.vue';
import dayjs from 'dayjs';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const report = ref<WasteReport | null>(null);
const isLoading = ref(true);
const feedbackMessage = ref('');
const errorMessage = ref('');

const isTransitionModalOpen = ref(false);
const isTransitioning = ref(false);
const isDeleteModalOpen = ref(false);
const lightboxUrl = ref<string | null>(null);

const transitionForm = ref<{
  status: ReportStatusType;
  notes: string;
}>({
  status: 'IN_PROGRESS',
  notes: '',
});

const canEditOrDelete = computed(() => {
  if (!report.value || !authStore.user) return false;
  if (authStore.isAdmin) return true;
  return report.value.user?.uuid === authStore.user.uuid && report.value.status === 'PENDING';
});

const canTransition = computed(() => {
  return report.value?.status === 'PENDING' || report.value?.status === 'IN_PROGRESS';
});

const availableNextStatuses = computed(() => {
  if (!report.value) return [];
  if (report.value.status === 'PENDING') {
    return [
      { value: 'IN_PROGRESS', label: 'In Progress (Dispatch Team)' },
      { value: 'REJECTED', label: 'Rejected (Invalid / Duplicate)' },
    ];
  }
  if (report.value.status === 'IN_PROGRESS') {
    return [
      { value: 'COMPLETED', label: 'Completed (Waste Cleaned)' },
    ];
  }
  return [];
});

const loadReport = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const uuid = route.params.uuid as string;
    report.value = await reportService.fetchReport(uuid);
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to load report.';
  } finally {
    isLoading.value = false;
  }
};

const openTransitionModal = () => {
  if (availableNextStatuses.value.length > 0) {
    transitionForm.value.status = availableNextStatuses.value[0].value as ReportStatusType;
  }
  transitionForm.value.notes = '';
  isTransitionModalOpen.value = true;
};

const executeTransition = async () => {
  if (!report.value) return;
  isTransitioning.value = true;
  errorMessage.value = '';
  try {
    const updated = await reportService.transitionStatus(report.value.uuid, transitionForm.value);
    report.value = updated;
    feedbackMessage.value = `Report status transitioned to ${updated.status} successfully!`;
    isTransitionModalOpen.value = false;
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || err.response?.data?.errors?.status?.[0] || 'Failed to transition status.';
  } finally {
    isTransitioning.value = false;
  }
};

const executeDelete = async () => {
  if (!report.value) return;
  try {
    await reportService.deleteReport(report.value.uuid);
    isDeleteModalOpen.value = false;
    router.push('/reports');
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to delete report.';
    isDeleteModalOpen.value = false;
  }
};

const deleteImage = async (imageUuid: string) => {
  if (!report.value) return;
  if (!confirm('Are you sure you want to remove this photo?')) return;
  try {
    await reportService.deleteImage(report.value.uuid, imageUuid);
    report.value.images = report.value.images?.filter((i) => i.uuid !== imageUuid);
    feedbackMessage.value = 'Photo removed successfully.';
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to remove photo.';
  }
};

const openLightbox = (url: string) => {
  lightboxUrl.value = url;
};

const formatDate = (date?: string) => {
  if (!date) return '-';
  return dayjs(date).format('MMMM D, YYYY [at] HH:mm');
};

const formatDateShort = (date?: string) => {
  if (!date) return '-';
  return dayjs(date).format('MMM D, HH:mm');
};

onMounted(() => {
  loadReport();
});
</script>

