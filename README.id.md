# 📚 Laravel E-Learning & CBT Platform

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

> 🇬🇧 [Read in English](README.md)

Platform **E-Learning** dan **Computer Based Test (CBT)** berbasis web untuk manajemen pembelajaran antara **Admin**, **Guru**, dan **Siswa**. Dibangun menggunakan Laravel 12, Livewire 3, dan Laravel Reverb untuk fitur real-time.

---

## ✨ Fitur Utama

### 👨‍💼 Panel Admin (`/admin`)
| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Statistik: total guru, siswa, kelas, mata pelajaran |
| **Guru** | CRUD lengkap + otomatis buat akun login |
| **Siswa** | CRUD + filter kelas + **📥 Import Excel** |
| **Kelas** | CRUD + wali kelas + jumlah siswa |
| **Mapel** | CRUD + kode mapel + assign guru pengampu |
| **Pengumuman** | CRUD + target audiens (semua/guru/siswa) + toggle publish |
| **Impersonate** | Login sebagai user lain untuk debugging |
| **Keamanan** | Blokir pengguna & alamat IP |

### 👨‍🏫 Panel Guru (`/teacher`)
| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Statistik mengajar + ujian mendatang + tugas aktif |
| **Materi** | CRUD + upload file + 5 tipe (dokumen/video/teks/link/audio) + tracking views |
| **Tugas** | CRUD + deadline + manajemen status + review submission |
| **Ujian CBT** | CRUD + durasi + KKM + acak soal/opsi + retry |
| **Bank Soal** | CRUD + pilihan ganda (A-E) / isian / essay + tingkat kesulitan |
| **Monitor Ujian** | Monitoring progres siswa secara real-time (auto-refresh 5 detik) |
| **Cetak** | Kartu ujian, daftar hadir, berita acara |
| **💬 Diskusi** | Chat real-time dengan siswa per tugas (Laravel Reverb) |

### 👨‍🎓 Panel Siswa (`/student`) — Desain Mobile-First
| Fitur | Deskripsi |
|-------|-----------|
| **Beranda** | Ujian mendatang, tugas aktif, pengumuman |
| **Materi** | Jelajahi & unduh materi pembelajaran |
| **Tugas** | Lihat tugas + submit file |
| **Ujian** | Jadwal ujian + riwayat nilai |
| **Nilai** | Rata-rata per mapel, progress bar, status lulus/tidak lulus |
| **💬 Diskusi** | Chat real-time dengan guru per tugas (Laravel Reverb) |

### 🖥️ Sistem Ujian CBT
```
Halaman Ujian → Konfirmasi → Mode Ujian
                               ├── ⏱️ Timer Countdown (auto-submit saat habis)
                               ├── 🔒 Anti-Cheat (fullscreen + deteksi pindah tab)
                               ├── 📍 Navigasi Soal (badge berwarna)
                               ├── 💾 Auto-Save (setiap klik jawaban)
                               └── ✅ Penilaian Otomatis (pilihan ganda)
```

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Backend** | PHP 8.2+, Laravel 12 |
| **Frontend** | Livewire 3, Volt, Alpine.js, Tailwind CSS 3 |
| **Real-time** | Laravel Reverb (WebSocket) |
| **Database** | MySQL / SQLite |
| **Auth** | Laravel Breeze |
| **Roles** | Spatie Laravel Permission |
| **Excel** | Maatwebsite Excel |
| **Build** | Vite 7 |

---

## 🏗️ Arsitektur Real-time Chat (Laravel Reverb)

Fitur diskusi tugas menggunakan **WebSocket** untuk komunikasi real-time antara guru dan siswa.

### Alur Pengiriman Pesan

```
┌─────────────┐     ┌──────────────┐     ┌──────────────┐     ┌─────────────┐
│  User kirim  │────▶│   Livewire   │────▶│   Database   │     │             │
│    pesan     │     │   send()     │     │   (MySQL)    │     │   Laravel   │
└─────────────┘     └──────┬───────┘     └──────────────┘     │   Reverb    │
                           │                                   │  (WebSocket │
                           │  broadcast(DiscussionMessageSent) │   Server)   │
                           └──────────────────────────────────▶│             │
                                                               └──────┬──────┘
                                                                      │
                                              WebSocket push (instan) │
                                                                      ▼
                                                        ┌─────────────────────┐
                                                        │  Semua user lain    │
                                                        │  di halaman diskusi │
                                                        │  → auto refresh     │
                                                        └─────────────────────┘
```

### Komponen Utama

| File | Fungsi |
|------|--------|
| `app/Events/DiscussionMessageSent.php` | Event broadcast saat pesan dikirim |
| `app/Events/DiscussionMessageDeleted.php` | Event broadcast saat guru hapus pesan |
| `routes/channels.php` | Otorisasi private channel per tugas |
| `app/Livewire/Student/AssignmentDiscussionStudent.php` | Component siswa + Echo listener |
| `app/Livewire/Teacher/AssignmentDiscussionTeacher.php` | Component guru + Echo listener |
| `resources/js/bootstrap.js` | Setup Laravel Echo + Reverb client |

### Otorisasi Channel

Setiap diskusi tugas menggunakan **private channel**: `assignment.{assignmentId}.discussion`

Hanya pengguna yang berhak yang bisa subscribe:
- **Guru** — semua guru yang sudah login
- **Siswa** — siswa yang terdaftar di kelas tugas terkait

---

## 🚀 Instalasi

### Prasyarat
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL (atau SQLite untuk development)

### Setup Cepat

```bash
# Clone repository
git clone <repo-url>
cd laravel-elearning

# Satu perintah (install deps, generate key, migrasi, build assets)
composer setup
```

### Setup Manual

```bash
# Install dependensi PHP
composer install

# Salin file environment & generate app key
cp .env.example .env
php artisan key:generate

# Konfigurasi database di .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=elearning_cbt
# DB_USERNAME=root
# DB_PASSWORD=

# Jalankan migrasi (dengan data demo)
php artisan migrate:fresh --seed

# Install & build frontend
npm install
npm run build
```

### Server Development

```bash
composer dev
```

Perintah ini menjalankan **5 proses** secara bersamaan:

| Proses | Warna | Keterangan |
|--------|-------|------------|
| `php artisan serve` | 🔵 Biru | HTTP server |
| `php artisan queue:listen` | 🟣 Ungu | Queue worker |
| `php artisan pail` | 🔴 Merah | Log viewer |
| `npm run dev` | 🟠 Oranye | Vite dev server |
| `php artisan reverb:start` | 🟢 Hijau | WebSocket server |

---

## 🔑 Akun Demo

Setelah menjalankan `migrate:fresh --seed`:

| Role | Email | Password | URL |
|------|-------|----------|-----|
| Admin | `admin@sekolah.id` | `password` | `/admin` |
| Guru | `budi@sekolah.id` | `password` | `/teacher` |
| Guru | `siti@sekolah.id` | `password` | `/teacher` |
| Siswa | `andi.pratama1@siswa.id` | `password` | `/student` |

---

## 📥 Format Import Excel Siswa

| Kolom | Wajib | Contoh |
|-------|-------|--------|
| nama | ✅ | Andi Pratama |
| email | ✅ | andi@siswa.id |
| nis | ✅ | 20250001 |
| kelas | ✅ | X IPA 1 |
| nisn | | 0012345678 |
| gender | | L / P |
| tanggal_lahir | | 2009-05-15 |
| alamat | | Jl. Merdeka No. 1 |
| password | | *(default: password)* |

---

## 🧪 Testing

```bash
composer test
```

Lihat [docs/TESTING.md](docs/TESTING.md) untuk panduan testing manual secara lengkap.

---

## 📂 Struktur Folder Utama

```
app/
├── Events/                    # Broadcast events (Reverb)
├── Imports/
│   └── StudentImport.php      # Import siswa dari Excel
├── Http/
│   ├── Controllers/           # Impersonate, dll.
│   └── Middleware/             # Role, BlockedUser, BlockedIp
├── Livewire/
│   ├── Admin/                 # 6 komponen (Dashboard, CRUD guru/siswa/kelas/mapel/pengumuman)
│   ├── Student/               # 8 komponen (Dashboard, materi, tugas, ujian, nilai, diskusi)
│   └── Teacher/               # 11 komponen (Dashboard, materi, tugas, ujian, bank soal, monitor, diskusi)
├── Models/                    # 17 model (User, Assignment, Exam, dll.)
routes/
├── admin.php                  # Route admin
├── teacher.php                # Route guru
├── student.php                # Route siswa
├── channels.php               # Otorisasi broadcast channel
docs/
├── REVERB-PRODUCTION.md       # Panduan deployment production Reverb
├── SECURITY-AUDIT.md          # Dokumentasi audit keamanan
└── TESTING.md                 # Panduan testing manual
```

---

## 🚢 Deployment Production

Lihat [docs/REVERB-PRODUCTION.md](docs/REVERB-PRODUCTION.md) untuk panduan lengkap deployment ke production termasuk konfigurasi Supervisor dan Nginx.

---

## 📄 Lisensi

Proyek ini merupakan perangkat lunak open-source yang dilisensikan di bawah [lisensi MIT](https://opensource.org/licenses/MIT).
