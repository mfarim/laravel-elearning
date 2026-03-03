<div>
  {{-- Greeting --}}
  <div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
    <p class="text-sm text-gray-500 mt-1">Selamat datang kembali! Berikut ringkasan data pembelajaran Anda.</p>
  </div>

  {{-- Stats Grid --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Materi</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalMaterials }}</p>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50">
          <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Tugas</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalAssignments }}</p>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50">
          <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Ujian</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalExams }}</p>
        </div>
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-50">
          <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Two columns --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Ujian Mendatang --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">Ujian Mendatang</h3>
        <a href="{{ route('teacher.exams.index') }}"
          class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Lihat semua →</a>
      </div>
      <ul class="divide-y divide-gray-100">
        @forelse($upcomingExams as $exam)
          <li class="px-5 py-3.5">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $exam->title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $exam->duration_minutes }} menit ·
                  {{ $exam->total_questions ?? '?' }} soal</p>
              </div>
              <span
                class="flex-shrink-0 text-xs font-medium text-emerald-600 bg-emerald-50 rounded-full px-2 py-0.5">{{ $exam->start_at->format('d M H:i') }}</span>
            </div>
          </li>
        @empty
          <li class="px-5 py-8 text-center">
            <p class="text-sm text-gray-400">Tidak ada ujian mendatang</p>
          </li>
        @endforelse
      </ul>
    </div>

    {{-- Tugas Aktif --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">Tugas Aktif</h3>
        <a href="{{ route('teacher.assignments.index') }}"
          class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Lihat semua →</a>
      </div>
      <ul class="divide-y divide-gray-100">
        @forelse($pendingAssignments as $a)
          <li class="px-5 py-3.5">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $a->title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Deadline: {{ $a->due_date->format('d M Y H:i') }}</p>
              </div>
              <span
                class="flex-shrink-0 text-xs font-medium text-blue-600 bg-blue-50 rounded-full px-2 py-0.5">{{ $a->submissions_count }}
                submitted</span>
            </div>
          </li>
        @empty
          <li class="px-5 py-8 text-center">
            <p class="text-sm text-gray-400">Tidak ada tugas aktif</p>
          </li>
        @endforelse
      </ul>
    </div>
  </div>
</div>