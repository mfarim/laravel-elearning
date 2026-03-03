<div x-data="examEngine({{ $remainingSeconds }})" x-init="init()" class="min-h-screen flex flex-col">
  @if(!$examStarted)
    {{-- Confirmation Screen --}}
    <div class="flex-1 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl shadow-lg max-w-md w-full p-6">
        <div class="text-center mb-6">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4"><span
              class="text-3xl">📝</span></div>
          <h2 class="text-xl font-bold text-gray-900">{{ $examination->title }}</h2>
          <p class="text-sm text-gray-500 mt-1">{{ $examination->subject?->name }}</p>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-6">
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Durasi</p>
            <p class="text-lg font-bold text-gray-900">{{ $examination->duration_minutes }}'</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Jumlah Soal</p>
            <p class="text-lg font-bold text-gray-900">{{ $questions->count() }}</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">KKM</p>
            <p class="text-lg font-bold text-gray-900">{{ $examination->passing_score }}</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-xs text-gray-500">Tipe</p>
            <p class="text-lg font-bold text-gray-900">{{ strtoupper($examination->type) }}</p>
          </div>
        </div>
        <div class="bg-yellow-50 rounded-xl p-3 mb-6 text-xs text-yellow-800">
          <p class="font-semibold mb-1">⚠️ Perhatian:</p>
          <ul class="list-disc list-inside space-y-0.5">
            <li>Jawaban tersimpan otomatis</li>
            @if($examination->shuffle_questions)
            <li>Urutan soal diacak</li>@endif
            @if($examination->shuffle_options)
            <li>Urutan pilihan jawaban diacak</li>@endif
            <li>Pastikan koneksi internet stabil</li>
            <li>Jangan pindah tab / minimize browser</li>
            <li>Mode fullscreen akan aktif otomatis</li>
          </ul>
        </div>
        <button wire:click="startExam"
          class="w-full rounded-xl bg-blue-600 py-3 text-sm font-bold text-white hover:bg-blue-500 transition">Mulai Ujian
          →</button>
        <a href="{{ route('student.exams') }}" class="block text-center mt-3 text-sm text-gray-400 hover:text-gray-600">←
          Kembali</a>
      </div>
    </div>
  @else
    {{-- Exam Mode --}}
    <div class="sticky top-0 z-50 bg-blue-600 text-white px-4 py-2">
      <div class="max-w-2xl mx-auto flex items-center justify-between">
        <div>
          <p class="text-xs opacity-80">{{ $examination->title }}</p>
          <p class="text-sm font-bold">Soal {{ $currentIndex + 1 }}/{{ $questions->count() }}</p>
        </div>
        <div class="flex items-center gap-3">
          {{-- Timer --}}
          <div class="flex items-center gap-1" :class="timerColor">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-mono font-bold" x-text="timerDisplay"></span>
          </div>
          <span class="text-xs bg-blue-500 rounded-full px-3 py-1">{{ $answeredCount }}/{{ $questions->count() }}</span>
          <button wire:click="confirmFinish"
            class="text-xs bg-red-500 hover:bg-red-600 rounded-full px-3 py-1 font-semibold">Selesai</button>
        </div>
      </div>
    </div>

    {{-- Question Number Navigation --}}
    <div class="bg-white border-b px-4 py-2 overflow-x-auto">
      <div class="flex gap-1.5 max-w-2xl mx-auto">
        @foreach($questions as $i => $q)
          <button wire:click="goTo({{ $i }})"
            class="flex-shrink-0 w-8 h-8 rounded-lg text-xs font-bold transition
                    {{ $i === $currentIndex ? 'bg-blue-600 text-white' : (isset($answers[$q->id]) && $answers[$q->id] !== '' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-gray-100 text-gray-600') }}">
            {{ $i + 1 }}
          </button>
        @endforeach
      </div>
    </div>

    {{-- Question Content --}}
    <div class="flex-1 p-4">
      <div class="max-w-2xl mx-auto">
        @if($currentQuestion)
          <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
            <p class="text-sm font-bold text-gray-500 mb-2">Soal {{ $currentIndex + 1 }}</p>
            <div class="text-base text-gray-900 leading-relaxed">{!! nl2br(e($currentQuestion->question_text)) !!}</div>
          </div>

          @if($currentQuestion->question_type === 'multiple_choice' && $currentQuestion->options)
            <div class="space-y-2.5">
              @foreach($currentQuestion->options as $i => $option)
                @if($option)
                  <button wire:click="saveAnswer({{ $currentQuestion->id }}, '{{ chr(65 + $i) }}')"
                    class="w-full text-left rounded-xl border-2 p-4 transition
                              {{ ($answers[$currentQuestion->id] ?? '') === chr(65 + $i) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-blue-300' }}">
                    <div class="flex items-center gap-3">
                      <span
                        class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                      {{ ($answers[$currentQuestion->id] ?? '') === chr(65 + $i) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        {{ chr(65 + $i) }}
                      </span>
                      <span class="text-sm text-gray-900">{{ $option }}</span>
                    </div>
                  </button>
                @endif
              @endforeach
            </div>
          @else
            <div class="bg-white rounded-2xl shadow-sm p-4">
              <textarea wire:change="saveAnswer({{ $currentQuestion->id }}, $event.target.value)"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" rows="4"
                placeholder="Tulis jawaban...">{{ $answers[$currentQuestion->id] ?? '' }}</textarea>
            </div>
          @endif
        @endif
      </div>
    </div>

    {{-- Bottom Navigation --}}
    <div class="sticky bottom-0 bg-white border-t px-4 py-3">
      <div class="max-w-2xl mx-auto flex items-center justify-between">
        <button wire:click="prev" @if($currentIndex === 0) disabled @endif
          class="rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200 disabled:opacity-50">←
          Sebelumnya</button>
        @if($currentIndex < $questions->count() - 1)
          <button wire:click="next"
            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Selanjutnya
            →</button>
        @else
          <button wire:click="confirmFinish"
            class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-500">Selesai
            ✓</button>
        @endif
      </div>
    </div>

    {{-- Anti-Cheat Warning Modal --}}
    <template x-if="showViolationWarning">
      <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-red-900/80"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center">
          <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><span
              class="text-2xl">🚨</span></div>
          <h3 class="text-lg font-bold text-red-700 mb-2">Peringatan!</h3>
          <p class="text-sm text-gray-600 mb-1">Anda terdeteksi meninggalkan halaman ujian.</p>
          <p class="text-sm text-red-600 font-bold mb-4">Pelanggaran ke-<span x-text="violationCount"></span></p>
          <p class="text-xs text-gray-500 mb-4">Terlalu banyak pelanggaran dapat mengakibatkan ujian dianggap tidak sah.
          </p>
          <button @click="showViolationWarning = false"
            class="w-full rounded-xl bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-500">Kembali ke
            Ujian</button>
        </div>
      </div>
    </template>

    {{-- Finish Confirmation --}}
    @if($showConfirm)
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/80"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center">
          <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4"><span
              class="text-2xl">⚠️</span></div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Selesaikan Ujian?</h3>
          <p class="text-sm text-gray-500 mb-1">{{ $answeredCount }} dari {{ $questions->count() }} soal dijawab</p>
          @if($answeredCount < $questions->count())
            <p class="text-sm text-red-600 font-medium mb-4">{{ $questions->count() - $answeredCount }} soal belum dijawab!
          </p>@else<p class="text-sm text-green-600 font-medium mb-4">Semua soal sudah dijawab 👍</p>@endif
          <div class="flex gap-3">
            <button wire:click="$set('showConfirm', false)"
              class="flex-1 rounded-xl bg-gray-100 py-2.5 text-sm font-semibold text-gray-700">Kembali</button>
            <button wire:click="finishExam"
              class="flex-1 rounded-xl bg-green-600 py-2.5 text-sm font-semibold text-white hover:bg-green-500">Ya,
              Selesai</button>
          </div>
        </div>
      </div>
    @endif
  @endif
</div>

<script>
  function examEngine(remainingSeconds) {
    return {
      seconds: remainingSeconds,
      timerDisplay: '',
      timerColor: '',
      violationCount: 0,
      showViolationWarning: false,
      timerInterval: null,

      init() {
        if (this.seconds > 0) {
          this.updateDisplay();
          this.timerInterval = setInterval(() => {
            this.seconds--;
            this.updateDisplay();
            if (this.seconds <= 0) {
              clearInterval(this.timerInterval);
              // Auto-submit
              this.$wire.autoFinish();
            }
          }, 1000);
        }

        // Anti-cheat: visibility change (tab switch)
        document.addEventListener('visibilitychange', () => {
          if (document.hidden) {
            this.violationCount++;
            this.showViolationWarning = true;
            this.$wire.logViolation();
          }
        });

        // Anti-cheat: request fullscreen
        try {
          const el = document.documentElement;
          if (el.requestFullscreen) el.requestFullscreen();
          else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
        } catch (e) { }

        // Anti-cheat: detect fullscreen exit
        document.addEventListener('fullscreenchange', () => {
          if (!document.fullscreenElement && this.seconds > 0) {
            this.violationCount++;
            this.showViolationWarning = true;
            this.$wire.logViolation();
            // Try to re-enter fullscreen
            try { document.documentElement.requestFullscreen(); } catch (e) { }
          }
        });
      },

      updateDisplay() {
        const m = Math.floor(this.seconds / 60);
        const s = this.seconds % 60;
        this.timerDisplay = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

        if (this.seconds <= 60) this.timerColor = 'text-red-300 animate-pulse';
        else if (this.seconds <= 300) this.timerColor = 'text-yellow-300';
        else this.timerColor = 'text-white';
      }
    };
  }
</script>