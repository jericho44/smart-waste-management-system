<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between text-xs">
      <span class="text-slate-600 font-medium">Click on the map or drag the pin to set the waste location:</span>
      <button
        type="button"
        @click="locateUser"
        :disabled="isLocating"
        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition font-medium cursor-pointer"
      >
        <span>📍</span>
        <span>{{ isLocating ? 'Locating...' : 'Use My Current Location' }}</span>
      </button>
    </div>

    <!-- Map Container -->
    <div
      ref="mapContainer"
      class="h-72 w-full rounded-2xl border border-slate-300 overflow-hidden shadow-xs relative z-10"
    ></div>

    <!-- Coordinate readout -->
    <div class="flex items-center gap-4 text-xs font-mono text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
      <div>Latitude: <strong class="text-slate-800">{{ lat.toFixed(6) }}</strong></div>
      <div>Longitude: <strong class="text-slate-800">{{ lng.toFixed(6) }}</strong></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
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
    lat: -6.2088,
    lng: 106.8456,
    zoom: 14,
  }
);

const emit = defineEmits<{
  (e: 'update:lat', val: number): void;
  (e: 'update:lng', val: number): void;
}>();

const mapContainer = ref<HTMLElement | null>(null);
const isLocating = ref(false);

let map: L.Map | null = null;
let marker: L.Marker | null = null;

onMounted(() => {
  if (!mapContainer.value) return;

  map = L.map(mapContainer.value).setView([props.lat, props.lng], props.zoom);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map);

  marker = L.marker([props.lat, props.lng], {
    draggable: true,
  }).addTo(map);

  marker.on('dragend', () => {
    if (!marker) return;
    const pos = marker.getLatLng();
    emit('update:lat', pos.lat);
    emit('update:lng', pos.lng);
  });

  map.on('click', (e: L.LeafletMouseEvent) => {
    if (!marker) return;
    marker.setLatLng(e.latlng);
    emit('update:lat', e.latlng.lat);
    emit('update:lng', e.latlng.lng);
  });
});

watch(
  () => [props.lat, props.lng],
  ([newLat, newLng]) => {
    if (marker && map) {
      const currentPos = marker.getLatLng();
      if (Math.abs(currentPos.lat - newLat) > 0.00001 || Math.abs(currentPos.lng - newLng) > 0.00001) {
        marker.setLatLng([newLat, newLng]);
        map.panTo([newLat, newLng]);
      }
    }
  }
);

const locateUser = () => {
  if (!navigator.geolocation) {
    alert('Geolocation is not supported by your browser.');
    return;
  }

  isLocating.value = true;
  navigator.geolocation.getCurrentPosition(
    (position) => {
      isLocating.value = false;
      const { latitude, longitude } = position.coords;
      emit('update:lat', latitude);
      emit('update:lng', longitude);
      if (marker && map) {
        marker.setLatLng([latitude, longitude]);
        map.setView([latitude, longitude], 16);
      }
    },
    () => {
      isLocating.value = false;
      alert('Unable to retrieve your location. Please select on the map manually.');
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
};

onBeforeUnmount(() => {
  if (map) {
    map.remove();
    map = null;
  }
});
</script>

