# PANDUAN PENGGUNAAN POSTMAN COLLECTION
## Smart Waste Management System REST API (v1)

Repositori ini telah dilengkapi dengan berkas Postman Collection v2.1.0 dan Postman Environment yang siap digunakan untuk menguji seluruh 30 endpoint API secara interaktif.

---

## 📁 Berkas Postman Tersedia

1. **[`smart_waste_management_postman_collection.json`](./smart_waste_management_postman_collection.json)**
   * Berisi seluruh endpoint lengkap yang dikelompokkan berdasarkan modul:
     - `0. Health Check` (Pemeriksaan kesiapan sistem)
     - `1. Authentication` (Login Admin/Citizen, Register, Profile, Change Password, Logout)
     - `2. Dashboard` (Metrik Admin & Citizen)
     - `3. Waste Categories` (CRUD Kategori Sampah & Toggle Status)
     - `4. Waste Reports` (Pelaporan, Unggah Foto, State Machine Transisi Status, Riwayat Audit)
     - `5. User Management` (CRUD Pengguna & Toggle Status oleh Admin)
   * Dilengkapi skrip otomatis (*Tests script*) yang otomatis menangkap dan menyimpan token serta UUID ke variabel Postman.

2. **[`smart_waste_management_postman_environment.json`](./smart_waste_management_postman_environment.json)**
   * Berisi variabel lingkungan default: `base_url`, `token`, `admin_token`, `category_uuid`, `report_uuid`, `user_uuid`.

---

## 🚀 Cara Import ke Postman

1. Buka aplikasi **Postman** (Desktop atau Web).
2. Klik tombol **Import** di pojok kiri atas aplikasi Postman.
3. Tarik (*drag and drop*) atau pilih kedua berkas berikut dari direktori proyek:
   - `smart_waste_management_postman_collection.json`
   - `smart_waste_management_postman_environment.json`
4. Pilih environment **Smart Waste Management (Local)** di dropdown pojok kanan atas Postman.

---

## 💡 Alur Otomatisasi Variabel (Smart Automation)

Koleksi ini dirancang agar Anda **tidak perlu menyalin token atau UUID secara manual**:

1. **Otentikasi Otomatis**:
   - Jalankan request **`1. Authentication -> Login as Admin`**:
     - Token Sanctum admin akan **otomatis tersimpan** ke variabel `{{admin_token}}`.
   - Jalankan request **`1. Authentication -> Login as Citizen`**:
     - Token Sanctum warga akan **otomatis tersimpan** ke variabel `{{token}}`.
2. **Kategori Sampah**:
   - Menjalankan **`3. Waste Categories -> List All Categories`** atau **`Create Category`** otomatis memperbarui `{{category_uuid}}`.
3. **Laporan Sampah**:
   - Menjalankan **`4. Waste Reports -> Create Waste Report`** otomatis menangkap UUID laporan ke `{{report_uuid}}`.
   - Request selanjutnya seperti **`Get Report Details`**, **`Transition Status`**, atau **`Get Report Status History`** langsung mengonsumsi `{{report_uuid}}`.
4. **Manajemen Pengguna**:
   - Menjalankan **`5. User Management -> Create User`** otomatis menyimpan `{{user_uuid}}`.

---

## 🔄 Contoh Skenario Uji Alur Lengkap (E2E) di Postman

Berikut urutan eksekusi yang direkomendasikan untuk menguji seluruh siklus sistem:

1. **Health Check**:
   - Jalankan `GET /health` (Status 200 OK).
2. **Login Admin**:
   - Jalankan `POST /auth/login` (Admin).
3. **Login Citizen**:
   - Jalankan `POST /auth/login` (Citizen).
4. **Daftar Kategori**:
   - Jalankan `GET /categories` (Publik).
5. **Buat Laporan Baru**:
   - Buka `POST /reports`, pada tab *Body (form-data)* pilih foto lampiran opsional, lalu klik *Send*.
   - Status awal laporan adalah `PENDING`.
6. **Admin Meninjau & Mengubah Status**:
   - Buka `PATCH /reports/{{report_uuid}}/status` (IN_PROGRESS).
   - Laporan berpindah status ke `IN_PROGRESS` dan tim armada ditugaskan.
7. **Penyelesaian Laporan**:
   - Buka `PATCH /reports/{{report_uuid}}/status` (COMPLETED).
   - Laporan berpindah status ke `COMPLETED`.
8. **Audit Trail**:
   - Buka `GET /reports/{{report_uuid}}/history` untuk melihat rantai kronologis lengkap transisi status beserta catatan petugas dan tanggal.
9. **Dashboard Real-Time**:
   - Buka `GET /dashboard/admin` untuk melihat laporan tersebut terhitung dalam statistik penyelesaian.
