<div>
  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <a href="{{ route('teacher.exams.monitor', $examination) }}"
        class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">← Kembali ke Monitor</a>
      <h2 class="text-xl font-bold text-gray-900 mt-1">Penilaian Essay</h2>
      <p class="text-sm text-gray-500">{{ $examination->title }} · {{ $student->user->name }} ({{ $student->nis }})</p>
    </div>
    <div class="mt-3 sm:mt-0">
      <span class="inline-flex items-center rounded-md bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-700">
        ✏️ {{ $essayQuestions->count() }} Soal Essay
      </span>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-md bg-green-50 p-4">
      <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
  @endif

  {{-- PG Score Summary --}}
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Ringkasan Nilai PG (Auto-graded)</h3>
    <div class="flex items-center gap-4">
      <span class="text-lg font-bold text-blue-600">{{ $pgEarned }}/{{ $pgTotal }} poin</span>
      <span class="text-sm text-gray-500">({{ $pgTotal > 0 ? round(($pgEarned / $pgTotal) * 100) : 0 }}%)</span>
    </div>
  </div>

  {{-- Essay Questions --}}
  <form wire:submit="saveGrades">
    <div class="space-y-6">
      @foreach($essayQuestions as $i => $question)
        @php $answer = $essayAnswers->get($question->id); @endphp
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="bg-gray-50 px-6 py-3 border-b">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-700">Soal {{ $i + 1 }} — Maks {{ $question->points }}
                poin</span>
              @if($question->difficulty)
                <span
                  class="text-xs px-2 py-0.5 rounded-full
                      {{ $question->difficulty === 'easy' ? 'bg-green-100 text-green-700' : ($question->difficulty === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                  {{ ucfirst($question->difficulty) }}
                </span>
              @endif
            </div>
          </div>
          <div class="p-6 space-y-4">
            {{-- Question Text --}}
            <div>
              <p class="text-sm font-medium text-gray-900 mb-1">Pertanyaan:</p>
              <div class="text-sm text-gray-700 bg-gray-50 rounded p-3">{!! nl2br(e($question->question_text)) !!}</div>
            </div>

            {{-- Question Image --}}
            @if($question->image_path)
              <div>
                <img src="{{ asset('storage/' . $question->image_path) }}" alt="Gambar soal"
                  class="max-w-md rounded-lg border">
              </div>
            @endif

            {{-- Student Answer --}}
            <div>
              <p class="text-sm font-medium text-gray-900 mb-1">Jawaban Siswa:</p>
              @if($answer && $answer->answer_text)
                <div class="text-sm text-gray-700 bg-blue-50 rounded p-3 border border-blue-200">
                  {!! nl2br(e($answer->answer_text)) !!}</div>
              @else
                <div class="text-sm text-gray-400 bg-gray-50 rounded p-3 italic">Tidak ada jawaban</div>
              @endif
            </div>

            {{-- Grading --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nilai (maks: {{ $question->points }})</label>
                <input type="number" wire:model="grades.{{ $question->id }}.points" min="0" max="{{ $question->points }}"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Feedback (opsional)</label>
                <textarea wire:model="grades.{{ $question->id }}.feedback" rows="2"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                  placeholder="Komentar untuk siswa..."></textarea>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Submit --}}
    <div class="mt-6 flex items-center justify-between bg-white rounded-lg shadow p-4">
      <div class="text-sm text-gray-600">
        Setelah semua soal essay dinilai, klik tombol untuk menyimpan dan menghitung nilai akhir.
      </div>
      <button type="submit"
        class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
        ✅ Simpan & Selesaikan
      </button>
    </div>
  </form>
</div>