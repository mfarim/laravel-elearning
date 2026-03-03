<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
  public function run(): void
  {
    $announcements = [
      ['title' => 'Jadwal UTS Semester Genap 2025/2026', 'content' => 'Ujian Tengah Semester akan dilaksanakan pada tanggal 10-15 Maret 2026. Siswa diminta mempersiapkan diri dengan baik. Jadwal lengkap akan dibagikan oleh masing-masing wali kelas.', 'target' => 'all', 'is_published' => true],
      ['title' => 'Rapat Guru Bulanan', 'content' => 'Rapat guru bulan Februari akan dilaksanakan pada hari Jumat, 28 Februari 2026 pukul 14.00. Agenda: evaluasi pembelajaran dan persiapan UTS.', 'target' => 'teacher', 'is_published' => true],
      ['title' => 'Pengumpulan Tugas Daring', 'content' => 'Seluruh tugas daring harus dikumpulkan melalui portal E-Learning. Pastikan upload file sebelum deadline. File yang terlambat akan dikenakan pengurangan nilai.', 'target' => 'student', 'is_published' => true],
      ['title' => 'Libur Hari Raya', 'content' => 'Sekolah libur pada tanggal 28-31 Maret 2026 dalam rangka Hari Raya. Kegiatan belajar mengajar akan dimulai kembali pada 1 April 2026.', 'target' => 'all', 'is_published' => true],
      ['title' => 'Pelatihan CBT untuk Guru', 'content' => 'Pelatihan penggunaan sistem CBT akan diadakan minggu depan. Guru pengampu ujian wajib mengikuti.', 'target' => 'teacher', 'is_published' => false],
    ];

    foreach ($announcements as $a) {
      Announcement::create([
        ...$a,
        'published_at' => $a['is_published'] ? now()->subDays(rand(0, 7)) : null,
      ]);
    }
  }
}
