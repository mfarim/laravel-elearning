<div class="space-y-5">
  {{-- Available Exams --}}
  <div>
    <h2 class="text-lg font-bold text-gray-900 mb-3">📝 Ujian Tersedia</h2>
    @forelse($upcomingExams as $exam)
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-3">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900 text-sm">{{ $exam->title }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $exam->subject?->name }}</p>
          </div>
          <span
            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800">{{ strtoupper($exam->type) }}</span>
        </div>
        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
          <div class="bg-gray-50 rounded-lg p-2">
            <p class="text-xs text-gray-500">Durasi</p>
            <p class="text-sm font-bold text-gray-900">{{ $exam->duration_minutes }}'</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-2">
            <p class="text-xs text-gray-500">KKM</p>
            <p class="text-sm font-bold text-gray-900">{{ $exam->passing_score }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-2">
            <p class="text-xs text-gray-500">Soal</p>
            <p class="text-sm font-bold text-gray-900">{{ $exam->questions()->count() }}</p>
          </div>
        </div>
        <div class="mt-3 flex items-center justify-between">
          <div class="text-xs text-gray-500">
            📅 {{ $exam->start_at->format('d M H:i') }} — {{ $exam->end_at->format('H:i') }}
          </div>
          @if($exam->start_at->isPast() && $exam->end_at->isFuture())
            <a href="{{ route('student.exam.start', $exam->id) }}"
              class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500">Mulai
              Ujian →</a>
          @else
            <span class="text-xs text-gray-400 italic">Belum dimulai</span>
          @endif
        </div>
      </div>
    @empty
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-sm text-gray-400">Tidak ada
        ujian tersedia</div>
    @endforelse
  </div>

  {{-- Past Results --}}
  @if($pastAttempts->count())
    <div>
      <h2 class="text-lg font-bold text-gray-900 mb-3">📊 Riwayat Ujian</h2>
      @foreach($pastAttempts as $attempt)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-semibold text-gray-900 text-sm">{{ $attempt->examination?->title }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">{{ $attempt->examination?->subject?->name }} ·
                {{ $attempt->finished_at?->format('d M Y') }}</p>
            </div>
            <div class="text-right">
              <p class="text-lg font-bold {{ $attempt->is_passed ? 'text-green-600' : 'text-red-600' }}">
                {{ $attempt->score }}</p>
              <span
                class="text-xs {{ $attempt->is_passed ? 'text-green-600' : 'text-red-600' }}">{{ $attempt->is_passed ? '✓ Lulus' : '✗ Belum lulus' }}</span>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>