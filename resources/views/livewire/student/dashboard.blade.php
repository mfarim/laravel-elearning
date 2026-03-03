<div class="space-y-5">
  {{-- Upcoming Exams --}}
  @if($upcomingExams->count())
    <div>
      <h2 class="text-base font-bold text-gray-900 mb-3">📝 Ujian Mendatang</h2>
      <div class="space-y-3">
        @foreach($upcomingExams as $exam)
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-semibold text-gray-900">{{ $exam->title }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $exam->subject?->name }} · {{ $exam->duration_minutes }} menit</p>
              </div>
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800">{{ strtoupper($exam->type) }}</span>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
              </svg>
              {{ $exam->start_at->format('d M Y, H:i') }}
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  {{-- Active Assignments --}}
  <div>
    <h2 class="text-base font-bold text-gray-900 mb-3">📋 Tugas Aktif</h2>
    @forelse($activeAssignments as $a)
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-3">
        <h3 class="font-semibold text-gray-900">{{ $a->title }}</h3>
        <p class="text-xs text-gray-500 mt-1">{{ $a->subject?->name }}</p>
        <div class="mt-2 flex items-center justify-between">
          <span class="text-xs text-red-600 font-medium">Deadline: {{ $a->due_date->format('d M Y H:i') }}</span>
          <a href="{{ route('student.assignments') }}" class="text-xs text-blue-600 font-semibold">Lihat →</a>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center text-sm text-gray-400">Tidak ada
        tugas aktif 🎉</div>
    @endforelse
  </div>

  {{-- Announcements --}}
  @if($announcements->count())
    <div>
      <h2 class="text-base font-bold text-gray-900 mb-3">📢 Pengumuman</h2>
      @foreach($announcements as $ann)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-3">
          <h3 class="font-semibold text-gray-900 text-sm">{{ $ann->title }}</h3>
          <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ Str::limit(strip_tags($ann->content), 120) }}</p>
          <p class="text-xs text-gray-400 mt-2">{{ $ann->published_at?->diffForHumans() }}</p>
        </div>
      @endforeach
    </div>
  @endif
</div>