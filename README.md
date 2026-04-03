# Antricu — Queue Management System

**Antricu** Sistem Manajemen Antrian  yang dirancang untuk mengelola seluruh operasional antrian di instansi pelayanan publik (Puskesmas, Rumah Sakit, Klinik, dll) secara digital. Mulai dari pengambilan nomor antrian oleh pengunjung via Kiosk, pemanggilan antrian oleh petugas loket (Pemanggilan otomatis dengan Text-to-Speech), Pemantauan status antrian secara real-time melalui layar display dan scan QR Code. Dibangun dengan stack **Laravel 12 (Backend REST API)** dan **Vue.js 3 + Vite (Frontend SPA)**.

---

## Fitur & Aktor Terlibat

Sistem ini melibatkan 3 aktor utama:

1. **Admin**: Memiliki akses penuh ke seluruh fitur sistem, termasuk dashboard statistik, manajemen pengguna, manajemen layanan, manajemen loket, pengelolaan daftar antrian, laporan harian, dan pengaturan informasi instansi.
2. **Operator (Petugas Loket)**: Mengelola operasional antrian di loketnya masing-masing. Membuka/menutup loket, memanggil antrian selanjutnya, memanggil ulang (recall), memulai layanan, menyelesaikan layanan, dan melewati antrian (no-show).
3. **Pasien / Pengunjung**: Mengakses layar kiosk untuk mengambil nomor antrian (cetak struk via Bluetooth), memantau status antrian melalui display ruang tunggu, dan mengecek status antrian pribadi via scan QR Code.

---

## Demo Aplikasi

https://github.com/user-attachments/assets/426bb3a3-bb71-4f76-afb7-3d23b1008ee7

---

## Struktur & Perancangan Sistem

### 1. Database Entity Relationship Diagram (ERD)
Sistem database ini memiliki 5 tabel utama (`users`, `services`, `counters`, `queues`, `settings`) dengan integritas relasi antar-tabel.
![ERD Diagram](preview/diagram/erd.svg)

### 2. Use Case Diagram
Diagram ini memperlihatkan interaksi setiap aktor (Admin, Operator, Pasien) terhadap 14 fungsionalitas utama sistem.
![Use Case Diagram](preview/diagram/usecasediagram.svg)

### 3. User Flow Diagram
Alur jalannya interaksi user dari 3 perspektif: Pasien/Pengunjung, Operator (Petugas Loket), dan Admin.
![User Flow Diagram](preview/diagram/usersflows.svg)

---

## Menjalankan Aplikasi

Berjalan pada ekosistem menggunakan stack berikut:
- **PHP** >= 8.2
- **Composer** v2+
- **Node.js** v20+
- **Database** MySQL / MariaDB (Dianjurkan via Container/Docker)

### Menjalankan via Docker (Recommended)
Pada project ini **sudah disediakan Native Container Mapping** untuk proses development cepat.

```bash
docker compose up -d --build
```

Setelah dijalankan, Anda bisa mengakses url lokal berikut:
- **Frontend App:** `http://localhost:3000`
- **Backend/API:** `http://localhost:8000`
- **phpMyAdmin:** `http://localhost:8080`

### Menjalankan Tanpa Docker (Manual)

**1. Siapkan Database**

Buat database MySQL dengan nama `antricu`, pastikan service MySQL/MariaDB sudah berjalan di komputer Anda (misalnya via XAMPP atau Laragon).

**2. Setup Backend**

```bash
cd backend
composer install
copy .env.example .env
```

Sesuaikan file `.env` pada bagian database:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=antricu
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan:
```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan serve --port=8000
```

**3. Setup Frontend**

Buka terminal baru:
```bash
cd frontend
npm install
npm run dev
```

**4. Akses Aplikasi**
- **Backend/API:** `http://localhost:8000`
- **Frontend App:** `http://localhost:5173`

**Akun Default (dari Seeder):**
| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@gmail.com` | `password` |
| Operator (Ruang 1) | `petugas1@gmail.com` | `password` |
| Operator (Ruang 2) | `petugas2@gmail.com` | `password` |
| Operator (Ruang 3) | `petugas3@gmail.com` | `password` |
| Operator (Ruang 4) | `petugas4@gmail.com` | `password` |

---

## Struktur Project

```
antricu-queue-system/
|-- backend/                         # Laravel 12 (REST API)
|   |-- app/
|   |   |-- Http/
|   |   |   |-- Controllers/
|   |   |   |   |-- Admin/          # Controller (Dashboard, CRUD Layanan/Loket/Pengguna, Laporan)
|   |   |   |   |-- Auth/           # Login Controller (Sanctum SPA Authentication)
|   |   |   |   |-- Kiosk/          # Controller (Cetak Nomor Antrian, Display, Status QR)
|   |   |   |   |-- Operator/       # Controller (Panggil, Recall, Serve, Complete, Skip)
|   |   |   |-- Middleware/
|   |   |-- Models/                  # Model Eloquent (User, Service, Counter, Queue, Setting)
|   |-- config/
|   |   |-- cors.php                 # Konfigurasi CORS untuk SPA Authentication
|   |-- database/
|   |   |-- migrations/             # File migrasi 5 tabel + 1 alter
|   |   |-- seeders/                # Seeder data awal (instansi, layanan, loket, akun)
|   |-- routes/
|   |   |-- api.php                 # Seluruh definisi route REST API (v1)
|   |-- Dockerfile
|   |-- docker-entrypoint.sh
|   |-- .env.example
|   |-- composer.json
|
|-- frontend/                        # Vue.js 3 + Vite
|   |-- src/
|   |   |-- components/
|   |   |   |-- layout/             # AppLayout, Sidebar
|   |   |-- router/                 # Vue Router (role-based routing & guard)
|   |   |-- services/               # Axios instance & interceptor
|   |   |-- stores/                 # Pinia state management (authStore)
|   |   |-- views/
|   |   |   |-- admin/              # Halaman Admin (Dashboard, Layanan, Loket, Antrian, dll)
|   |   |   |-- auth/               # Halaman Login
|   |   |   |-- kiosk/              # Halaman Kiosk (Cetak Antrian, Display, Status QR)
|   |   |   |-- operator/           # Halaman Operator (Panel Panggilan Antrian)
|   |   |-- App.vue
|   |   |-- main.js
|   |-- Dockerfile
|   |-- nginx.conf
|   |-- package.json
|   |-- vite.config.js
|
|-- preview/
|   |-- diagram/
|   |   |-- erd.svg                 # Entity Relationship Diagram
|   |   |-- usecasediagram.svg      # Use Case Diagram
|   |   |-- usersflows.svg          # User Flow Diagram
|
|-- diagram.drawio                   # Source file diagram (editable)
|-- docker-compose.yml
|-- dev.bat
|-- .gitignore
|-- README.md
```

---

## Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

```
MIT License

Copyright (c) 2026 Cybha

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
