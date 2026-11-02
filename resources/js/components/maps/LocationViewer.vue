<template>
  <div class="space-y-2">
    <div
      ref="mapContainer"
      class="h-64 w-full rounded-2xl border border-slate-200 overflow-hidden shadow-xs relative z-10"
    ></div>

    <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
      <div class="font-mono">
        Coordinates: {{ lat.toFixed(6) }}, {{ lng.toFixed(6) }}
      </div>
      <a
        :href="`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-semibold"
      >
        <span>Open in Google Maps &rarr;</span>
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import L from 'leaflet';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

const props = withDefaults(
  defineProps<{
    lat: number;
    lng: number;
    zoom?: number;
  }>(),
  {
    zoom: 15,
  }
);

const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;

onMounted(() => {
  if (!mapContainer.value) return;

  map = L.map(mapContainer.value, {
    scrollWheelZoom: false,
  }).setView([props.lat, props.lng], props.zoom);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map);

  L.marker([props.lat, props.lng]).addTo(map);
});

onBeforeUnmount(() => {
  if (map) {
    map.remove();
    map = null;
  }
});
</script>

