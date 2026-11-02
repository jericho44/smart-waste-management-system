# Smart Waste Management System

> Platform Web Modern Berbasis Komunitas untuk Manajemen dan Pemantauan Laporan Sampah.

Proyek ini dibangun menggunakan arsitektur **Modular Monolith** dengan memisahkan secara bersih antara **Laravel 12 REST API** (`/api/v1`) dan **Vue 3 TypeScript SPA** (Single Page Application) yang didukung oleh **Tailwind CSS v4**, **TanStack Vue Query**, **ApexCharts**, serta **Leaflet OpenStreetMap**.

---

## 📑 Dokumentasi Lengkap

Untuk panduan mendalam mencakup seluruh proses dari awal perancangan, arsitektur, spesifikasi teknis, skema database, pengujian otomatis, hingga panduan operasional (*maintenance*) dan *troubleshooting*, silakan buka:

👉 **[DOCUMENTATION.md (Panduan Resmi Sistem)](./DOCUMENTATION.md)**  
👉 **[API_DOCUMENTATION.md (Spesifikasi Lengkap REST API)](./API_DOCUMENTATION.md)**  
👉 **[smart_waste_management_postman_collection.json (Postman Collection)](./smart_waste_management_postman_collection.json)** & **[POSTMAN_GUIDE.md](./POSTMAN_GUIDE.md)**

---

## 🚀 Fitur Utama

1. **Autentikasi & Otorisasi Ketat (Sanctum & Spatie)**:
   - Registrasi, Login, Profil Pengguna, dan Ganti Kata Sandi.
   - Hak akses berlapis berbasis Role (`Admin` dan `User`).
   - Token-based API session management dengan pencabutan token saat *logout*.

2. **Manajemen Laporan Sampah (Waste Reports)**:
   - Pembuatan laporan dengan pemilihan titik koordinat GPS interaktif (*Interactive Leaflet OSM map*).
   - Dukungan multi-upload gambar sampah dengan validasi MIME dan batas ukuran.
   - Warga dapat mengedit atau membatalkan laporannya hanya jika masih berstatus `PENDING`.
   - Laporan yang telah diproses tim (`IN_PROGRESS`) terkunci secara permanen demi integritas data lapangan.

3. **Alur Transisi Status & Audit Trail**:
   - Transisi status terstruktur: `PENDING` $\rightarrow$ `IN_PROGRESS` $\rightarrow$ `COMPLETED` atau `REJECTED`.
   - Pencatatan riwayat kronologis otomatis (*append-only*) di tabel `report_status_histories`.

4. **Dashboard & Visualisasi Data (ApexCharts)**:
   - **Dashboard Admin**: Metrik total laporan, status penyelesaian, total pengguna, persentase jenis sampah, dan diagram tren bulanan (Area Chart).
   - **Dashboard Warga**: Ringkasan personal, status pengajuan, dan riwayat aktivitas pelaporan sampah.

5. **Data Master Kategori & Pengguna**:
   - Pengelolaan Kategori Sampah (Organik, Plastik, Kertas) dengan pencegahan penghapusan jika kategori memiliki laporan aktif.
   - Manajemen akun pengguna terpusat dengan proteksi akun admin.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12, PHP 8.3+, PostgreSQL, Laravel Sanctum, Spatie Permission.
- **Frontend**: Vue 3.5, TypeScript, Vite 8, Tailwind CSS v4, Pinia, TanStack Vue Query, ApexCharts, Leaflet.
- **Testing**: PHPUnit 11 (45 Automated Feature Tests, 351 Assertions lulus 100%).
- **Linter & Formatter**: Laravel Pint (PSR-12).

---

## ⚡ Quick Start (Panduan Cepat)

### 1. Prasyarat
- PHP 8.3+ (Laragon)
- PostgreSQL (Port 5432, Database: `smart_waste_management`)
- Node.js & NPM

### 2. Setup Basis Data & Dependensi
```powershell
# Jalankan migrasi dan seeder awal
& "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe" artisan migrate:fresh --seed

# Hubungkan symlink storage publik
& "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe" artisan storage:link

# Build aset frontend produksi
npm.cmd run build
```

### 3. Menjalankan Server Lokal
```powershell
# Jalankan Backend Laravel (Terminal 1)
& "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe" artisan serve

# (Opsional) Jalankan Frontend Vite Dev Server jika sedang memodifikasi kode UI (Terminal 2)
npm.cmd run dev
```

Buka peramban di: **`http://127.0.0.1:8000`**

---

## 🔑 Akun Uji Coba Bawaan

| Akun | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@waste.local` | `Admin@123456` | Kelola seluruh laporan, perbarui status, kelola pengguna & kategori, pantau analitik sistem. |
| **Warga (Citizen)** | `user@waste.local` | `Password@123` | Membuat laporan, unggah foto sampah, lacak progres laporan personal. |

---

## 🧪 Menjalankan Pengujian (Automated Tests)

```powershell
& "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe" artisan test --compact
```

---

## 📄 Lisensi
Hak Cipta © 2026 Smart Waste Management System. Dilindungi oleh lisensi proyek internal.
