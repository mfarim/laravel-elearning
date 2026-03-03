<div>
  {{-- Greeting --}}
  <div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
    <p class="text-sm text-gray-500 mt-1">Selamat datang kembali! Berikut ringkasan data sekolah Anda.</p>
  </div>

  {{-- Stats Grid --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    {{-- Total Guru --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Total Guru</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalTeachers }}</p>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50">
          <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
          </svg>
        </div>
      </div>
    </div>

    {{-- Total Siswa --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Total Siswa</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalStudents }}</p>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-green-50">
          <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
          </svg>
        </div>
      </div>
    </div>

    {{-- Total Kelas --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Total Kelas</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalClassrooms }}</p>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-50">
          <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
          </svg>
        </div>
      </div>
    </div>

    {{-- Mata Pelajaran --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Mata Pelajaran</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalSubjects }}</p>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50">
          <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Two columns: Stats + Announcements --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Quick Stats --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
      <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900">Statistik Cepat</h3>
      </div>
      <div class="p-5 space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
            <span class="text-sm text-gray-600">Total Pengguna</span>
          </div>
          <span class="text-sm font-bold text-gray-900">{{ $totalUsers }}</span>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-green-500"></div>
            <span class="text-sm text-gray-600">Ujian Berlangsung</span>
          </div>
          <span class="text-sm font-bold text-gray-900">{{ $activeExams }}</span>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-amber-500"></div>
            <span class="text-sm text-gray-600">Total Guru</span>
          </div>
          <span class="text-sm font-bold text-gray-900">{{ $totalTeachers }}</span>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
            <span class="text-sm text-gray-600">Total Siswa</span>
          </div>
          <span class="text-sm font-bold text-gray-900">{{ $totalStudents }}</span>
        </div>
      </div>
    </div>

    {{-- Recent Announcements --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">Pengumuman Terbaru</h3>
        <a href="{{ route('admin.announcements.index') }}"
          class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Lihat semua →</a>
      </div>
      <ul class="divide-y divide-gray-100">
        @forelse($recentAnnouncements as $ann)
          <li class="px-5 py-3.5">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $ann->title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $ann->published_at?->diffForHumans() ?? 'Draft' }}</p>
              </div>
              <span
                class="flex-shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold
                        {{ $ann->target === 'all' ? 'bg-emerald-50 text-emerald-700' : ($ann->target === 'teacher' ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700') }}">
                {{ ucfirst($ann->target) }}
              </span>
            </div>
          </li>
        @empty
          <li class="px-5 py-8 text-center">
            <p class="text-sm text-gray-400">Belum ada pengumuman</p>
          </li>
        @endforelse
      </ul>
    </div>
  </div>
</div>