<template>
  <div class="space-y-16 pb-12">
    <!-- Hero Section -->
    <section class="bg-gradient-to-b from-white to-emerald-50/40 rounded-3xl p-8 sm:p-14 border border-emerald-100 shadow-sm text-center relative overflow-hidden">
      <!-- Decorative Glows -->
      <div class="absolute -top-28 -right-28 w-80 h-80 bg-emerald-200/50 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-28 -left-28 w-80 h-80 bg-teal-200/40 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Badge -->
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-200/80 mb-6 shadow-xs">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span>Platform Pengelolaan Sampah Cerdas & Berkelanjutan</span>
      </div>

      <!-- Main Headline -->
      <h1 class="text-4xl sm:text-6xl font-black text-slate-900 tracking-tight max-w-4xl mx-auto leading-tight sm:leading-none">
        Bersama Wujudkan Kota <br class="hidden sm:inline" />
        <span class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
          Bersih, Asri & Bebas Sampah
        </span>
      </h1>

      <!-- Subtitle -->
      <p class="mt-5 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
        Laporkan tumpukan sampah liar di sekitar Anda dengan foto dan koordinat GPS peta interaktif. Pantau proses penugasan armada kebersihan secara real-time hingga tuntas.
      </p>

      <!-- Primary Action Buttons -->
      <div class="mt-8 flex flex-wrap justify-center items-center gap-4">
        <template v-if="authStore.isAuthenticated">
          <router-link
            to="/reports/create"
            class="px-7 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md shadow-emerald-200 transition transform hover:-translate-y-0.5 flex items-center gap-2"
          >
            <span>+ Buat Laporan Sampah</span>
            <span>&rarr;</span>
          </router-link>
          <router-link
            to="/dashboard"
            class="px-7 py-3.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm border border-slate-200 rounded-xl transition shadow-xs"
          >
            Buka Dashboard Saya
          </router-link>
        </template>
        <template v-else>
          <router-link
            to="/login"
            class="px-7 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md shadow-emerald-200 transition transform hover:-translate-y-0.5 flex items-center gap-2"
          >
            <span>Laporkan Sampah Sekarang</span>
            <span>&rarr;</span>
          </router-link>
          <router-link
            to="/register"
            class="px-7 py-3.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm border border-slate-200 rounded-xl transition shadow-xs"
          >
            Daftar Akun Warga
          </router-link>
        </template>
      </div>

      <!-- Quick Feature Pills -->
      <div class="mt-10 pt-8 border-t border-slate-200/60 flex flex-wrap justify-center items-center gap-3 sm:gap-6 text-xs font-semibold text-slate-600">
        <div class="flex items-center gap-1.5 bg-white/80 px-3 py-1.5 rounded-lg border border-slate-200/60 shadow-2xs">
          <span class="text-emerald-600">📍</span>
          <span>Pin Lokasi GPS Akurat</span>
        </div>
        <div class="flex items-center gap-1.5 bg-white/80 px-3 py-1.5 rounded-lg border border-slate-200/60 shadow-2xs">
          <span class="text-blue-600">📸</span>
          <span>Foto Bukti Multi-Angle</span>
        </div>
        <div class="flex items-center gap-1.5 bg-white/80 px-3 py-1.5 rounded-lg border border-slate-200/60 shadow-2xs">
          <span class="text-amber-600">🚚</span>
          <span>Penugasan Armada Cepat</span>
        </div>
        <div class="flex items-center gap-1.5 bg-white/80 px-3 py-1.5 rounded-lg border border-slate-200/60 shadow-2xs">
          <span class="text-teal-600">♻️</span>
          <span>Daur Ulang Terpilah</span>
        </div>
      </div>
    </section>

    <!-- Key Metrics Counter Section -->
    <section class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs text-center">
        <p class="text-3xl sm:text-4xl font-black text-emerald-600">98.4%</p>
        <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mt-1">Tingkat Penyelesaian</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Laporan tertangani tuntas</p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs text-center">
        <p class="text-3xl sm:text-4xl font-black text-blue-600">&lt; 24 Jam</p>
        <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mt-1">Respon Armada</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Waktu rata-rata penjemputan</p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs text-center">
        <p class="text-3xl sm:text-4xl font-black text-amber-600">3 Kategori</p>
        <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mt-1">Klasifikasi Terpilah</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Organik, Plastik, & Kertas</p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs text-center">
        <p class="text-3xl sm:text-4xl font-black text-violet-600">100%</p>
        <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mt-1">Transparansi Proses</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Riwayat audit setiap status</p>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="space-y-8">
      <div class="text-center max-w-2xl mx-auto">
        <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-600">Alur Kerja Mudah</span>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
          Bagaimana Sistem Menangani Laporan Sampah?
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-2">
          Hanya butuh 4 langkah mudah dari temuan di lapangan hingga tumpukan sampah bersih terangkut.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Step 1 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-300 transition relative">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-sm mb-4">
            01
          </div>
          <h3 class="text-base font-bold text-slate-900">Foto & Tentukan Lokasi</h3>
          <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Ambil foto tumpukan sampah di tempat umum. Gunakan peta interaktif OpenStreetMap untuk menandai titik koordinat GPS yang presisi.
          </p>
        </div>

        <!-- Step 2 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-300 transition relative">
          <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-black text-sm mb-4">
            02
          </div>
          <h3 class="text-base font-bold text-slate-900">Pilih Kategori Sampah</h3>
          <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Klasifikasikan apakah sampah berupa Organik, Plastik, atau Kertas untuk memudahkan armada menyiapkan truk pemilah yang tepat.
          </p>
        </div>

        <!-- Step 3 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-300 transition relative">
          <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-black text-sm mb-4">
            03
          </div>
          <h3 class="text-base font-bold text-slate-900">Armada Diterjunkan</h3>
          <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Petugas dinas kebersihan memverifikasi laporan, memperbarui status menjadi <em>In Progress</em>, dan memberangkatkan tim armada ke lokasi.
          </p>
        </div>

        <!-- Step 4 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-300 transition relative">
          <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-black text-sm mb-4">
            04
          </div>
          <h3 class="text-base font-bold text-slate-900">Bersih & Daur Ulang</h3>
          <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Setelah lokasi bersih, laporan ditandai <em>Completed</em>. Sampah dibawa ke fasilitas pengolahan kompos atau bank daur ulang kota.
          </p>
        </div>
      </div>
    </section>

    <!-- Waste Categories Showcase -->
    <section class="space-y-8 bg-slate-50 -mx-4 sm:-mx-8 px-4 sm:px-8 py-12 rounded-3xl border border-slate-200/60">
      <div class="text-center max-w-2xl mx-auto">
        <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-600">Pemilahan Dari Sumber</span>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
          Kategori Sampah yang Dikelola
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-2">
          Pemilahan yang tepat mempercepat proses pengolahan kembali dan mencegah pencemaran lingkungan.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Organic -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition">
          <div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl mb-4">
              🍃
            </div>
            <h3 class="text-lg font-bold text-slate-900">Sampah Organik (Organic Waste)</h3>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
              Sisa makanan rumah tangga, sayuran, sisa buah-buahan, dedaunan gugur, dan sampah taman yang mudah membusuk.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100">
              <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md">
                ♻️ Diolah Jadi: Kompos & Pupuk Alami
              </span>
            </div>
          </div>
        </div>

        <!-- Plastic -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition">
          <div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl mb-4">
              🥤
            </div>
            <h3 class="text-lg font-bold text-slate-900">Sampah Plastik (Plastic Waste)</h3>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
              Botol air mineral, kemasan sachet detergen/makanan, kantong kresek, wadah makanan plastik, dan gelas sekali pakai.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100">
              <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md">
                ♻️ Diolah Jadi: Biji Plastik Daur Ulang
              </span>
            </div>
          </div>
        </div>

        <!-- Paper -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition">
          <div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mb-4">
              📦
            </div>
            <h3 class="text-lg font-bold text-slate-900">Sampah Kertas (Paper Waste)</h3>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
              Kardus paket pengiriman, kertas karton, koran bekas, majalah, dokumen kantor, dan kantong kertas kering.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100">
              <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md">
                ♻️ Diolah Jadi: Pulp Kertas & Karton Baru
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Dual Benefits: For Citizens & City Officials -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Citizen Benefits -->
      <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
          <span>👥 Untuk Warga Masyarakat</span>
        </div>
        <h3 class="text-xl font-black text-slate-900">Kemudahan Melapor & Pantau Lingkungan</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
          Tidak perlu lagi bingung menghubungi kontak birokrasi yang lambat saat melihat tempat sampah meluap di fasilitas umum.
        </p>
        <ul class="space-y-2.5 text-xs text-slate-600">
          <li class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Pelaporan cepat langsung dari browser HP tanpa instalasi aplikasi berat</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Status penanganan transparan (Menunggu Review, Ditindaklanjuti, Selesai)</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Riwayat kontribusi menjaga kebersihan lingkungan tersimpan di profil Anda</span>
          </li>
        </ul>
      </div>

      <!-- Administrator Benefits -->
      <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
          <span>🏢 Untuk Dinas Kebersihan & Pengelola</span>
        </div>
        <h3 class="text-xl font-black text-slate-900">Operasi Armada Lebih Cepat & Terdata</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
          Dashboard terpadu yang memetakan seluruh titik tumpukan sampah sehingga rute armada penjemputan dapat diatur secara efisien.
        </p>
        <ul class="space-y-2.5 text-xs text-slate-600">
          <li class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Peta geospasial real-time sebaran insiden sampah di seluruh penjuru kota</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Pencatatan riwayat audit (siapa petugas penanggung jawab dan catatan armada)</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>Analitik statistik tren bulanan dan rasio jenis sampah untuk perencanaan fasilitas</span>
          </li>
        </ul>
      </div>
    </section>

    <!-- Bottom Call to Action Banner -->
    <section class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-8 sm:p-12 text-white text-center shadow-lg shadow-emerald-200/50 relative overflow-hidden">
      <div class="max-w-2xl mx-auto space-y-4 relative z-10">
        <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
          Lihat Tumpukan Sampah di Sekitar Anda?
        </h2>
        <p class="text-xs sm:text-sm text-emerald-100 leading-relaxed">
          Jangan biarkan menumpuk dan mencemari lingkungan. Buat laporan sekarang dan bantu armada kebersihan menjangkau titik lokasi lebih cepat!
        </p>
        <div class="pt-2 flex flex-wrap justify-center gap-3">
          <router-link
            to="/reports/create"
            class="px-6 py-3 bg-white hover:bg-slate-100 text-emerald-800 font-bold text-xs sm:text-sm rounded-xl shadow-md transition transform hover:-translate-y-0.5"
          >
            + Laporkan Sampah Sekarang
          </router-link>
          <router-link
            to="/reports"
            class="px-6 py-3 bg-emerald-700/80 hover:bg-emerald-800 text-white font-bold text-xs sm:text-sm rounded-xl border border-emerald-400/40 transition"
          >
            Lihat Laporan Komunitas
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>
