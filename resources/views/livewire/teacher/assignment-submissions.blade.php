<div>
  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <a href="/teacher/assignments" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">← Kembali ke Penugasan</a>
      <h2 class="text-xl font-bold text-gray-900 mt-1">{{ $assignment->title }}</h2>
      <p class="text-sm text-gray-500">{{ $assignment->subject?->name }} · {{ $assignment->classroom?->name }} · Deadline: {{ $assignment->due_date->format('d M Y H:i') }}</p>
    </div>
    <div class="mt-3 sm:mt-0 flex items-center gap-3">
      <a href="/teacher/assignments/{{ $assignment->id }}/discussion"
        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">💬 Forum Diskusi</a>
      <span class="inline-flex items-center rounded-md bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">
        {{ $submissions->count() }}/{{ $students->count() }} Terkumpul
      </span>
    </div>
  </div>

  {{-- Table --}}
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIS</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Submit</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @foreach($students as $i => $student)
          @php $sub = $submissions->get($student->id); @endphp
          <tr class="{{ !$sub ? 'bg-gray-50/50' : '' }}">
            <td class="px-6 py-4 text-sm text-gray-500">{{ $i + 1 }}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $student->user?->name ?? '-' }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $student->nis }}</td>
            <td class="px-6 py-4 text-sm">
              @if(!$sub)
                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600">Belum</span>
              @elseif($sub->status === 'graded')
                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Dinilai</span>
              @elseif($sub->status === 'late')
                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800">Terlambat</span>
              @else
                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800">Terkumpul</span>
              @endif
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              {{ $sub?->submitted_at?->format('d M Y H:i') ?? '-' }}
            </td>
            <td class="px-6 py-4 text-sm">
              @if($sub?->file_path)
                <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" download
                  class="text-emerald-600 hover:text-emerald-800 font-medium inline-flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                  Download
                </a>
              @else
                <span class="text-gray-400">-</span>
              @endif
            </td>
            <td class="px-6 py-4 text-sm">
              @if($sub?->status === 'graded')
                <span class="font-bold text-emerald-700">{{ $sub->score }}/{{ $assignment->max_score }}</span>
              @elseif($sub)
                <span class="text-gray-400">Belum dinilai</span>
              @else
                <span class="text-gray-300">-</span>
              @endif
            </td>
            <td class="px-6 py-4 text-right text-sm">
              @if($sub)
                <button wire:click="startGrade({{ $sub->id }})" class="text-emerald-600 hover:text-emerald-900 font-medium">
                  {{ $sub->status === 'graded' ? 'Edit Nilai' : 'Beri Nilai' }}
                </button>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Grading Modal --}}
  @if($gradingId)
    @php $gradingSub = $submissions->first(fn($s) => $s->id === $gradingId); @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('gradingId', null)"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:w-full sm:max-w-md">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-1">📝 Penilaian</h3>
            <p class="text-sm text-gray-500 mb-4">{{ $gradingSub?->student?->user?->name }}</p>

            @if($gradingSub?->notes)
              <div class="bg-blue-50 rounded-lg p-3 mb-4 text-xs text-blue-800">
                <p class="font-semibold mb-0.5">Catatan siswa:</p>
                <p>{{ $gradingSub->notes }}</p>
              </div>
            @endif

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nilai (max: {{ $assignment->max_score }})</label>
                <input wire:model="score" type="number" min="0" max="{{ $assignment->max_score }}"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                @error('score')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Feedback (opsional)</label>
                <textarea wire:model="feedback" rows="3"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                  placeholder="Berikan komentar atau masukan..."></textarea>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <button wire:click="saveGrade"
              class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500 sm:ml-3 sm:w-auto">Simpan Nilai</button>
            <button wire:click="$set('gradingId', null)"
              class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 sm:mt-0 sm:w-auto">Batal</button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
