<template>
  <div class="space-y-3">
    <!-- Dropzone / Picker -->
    <div
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      :class="[
        'border-2 border-dashed rounded-2xl p-6 text-center transition cursor-pointer',
        isDragging
          ? 'border-emerald-500 bg-emerald-50/50'
          : 'border-slate-300 hover:border-emerald-400 bg-slate-50/50'
      ]"
      @click="triggerFileInput"
    >
      <input
        ref="fileInputRef"
        type="file"
        multiple
        accept="image/jpeg,image/png,image/webp,image/jpg"
        class="hidden"
        @change="handleFileChange"
      />

      <div class="space-y-2">
        <div class="text-3xl">📷</div>
        <div class="text-xs font-semibold text-slate-700">
          <span class="text-emerald-600">Click to upload</span> or drag and drop photos
        </div>
        <p class="text-[11px] text-slate-400">
          PNG, JPG, or WEBP (Max 5 photos, up to 5MB each)
        </p>
      </div>
    </div>

    <!-- Error message if file invalid -->
    <p v-if="uploadError" class="text-xs text-red-600 font-medium">{{ uploadError }}</p>

    <!-- Previews Grid -->
    <div v-if="previews.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 pt-2">
      <div
        v-for="(item, idx) in previews"
        :key="idx"
        class="relative rounded-xl overflow-hidden border border-slate-200 aspect-square group shadow-xs bg-slate-100"
      >
        <img
          :src="item.url"
          class="w-full h-full object-cover"
          alt="Preview thumbnail"
        />

        <button
          type="button"
          @click.stop="removeFile(idx)"
          class="absolute top-1 right-1 h-6 w-6 rounded-full bg-slate-900/70 hover:bg-red-600 text-white flex items-center justify-center text-xs transition cursor-pointer"
          title="Remove photo"
        >
          &times;
        </button>

        <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded bg-slate-900/60 text-[10px] text-white font-mono">
          {{ formatSize(item.file.size) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = withDefaults(
  defineProps<{
    maxFiles?: number;
  }>(),
  {
    maxFiles: 5,
  }
);

const emit = defineEmits<{
  (e: 'update:files', files: File[]): void;
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const uploadError = ref('');
const selectedFiles = ref<File[]>([]);
const previews = ref<Array<{ file: File; url: string }>>([]);

const triggerFileInput = () => {
  fileInputRef.value?.click();
};

const validateAndAddFiles = (filesList: FileList | File[]) => {
  uploadError.value = '';
  const newFiles = Array.from(filesList);

  for (const file of newFiles) {
    if (selectedFiles.value.length >= props.maxFiles) {
      uploadError.value = `You can upload a maximum of ${props.maxFiles} photos.`;
      break;
    }

    if (!file.type.startsWith('image/')) {
      uploadError.value = `File "${file.name}" is not an image.`;
      continue;
    }

    if (file.size > 5 * 1024 * 1024) {
      uploadError.value = `File "${file.name}" exceeds the 5MB size limit.`;
      continue;
    }

    selectedFiles.value.push(file);
    previews.value.push({
      file,
      url: URL.createObjectURL(file),
    });
  }

  emit('update:files', selectedFiles.value);
};

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files) {
    validateAndAddFiles(target.files);
    target.value = '';
  }
};

const handleDrop = (e: DragEvent) => {
  isDragging.value = false;
  if (e.dataTransfer?.files) {
    validateAndAddFiles(e.dataTransfer.files);
  }
};

const removeFile = (index: number) => {
  URL.revokeObjectURL(previews.value[index].url);
  previews.value.splice(index, 1);
  selectedFiles.value.splice(index, 1);
  emit('update:files', selectedFiles.value);
};

const formatSize = (bytes: number) => {
  if (bytes < 1024 * 1024) {
    return (bytes / 1024).toFixed(0) + 'KB';
  }
  return (bytes / (1024 * 1024)).toFixed(1) + 'MB';
};
</script>

