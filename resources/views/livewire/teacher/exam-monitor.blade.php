<div wire:poll.5s>
  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Monitor Ujian</h2>
      <p class="text-sm text-gray-500 mt-1">{{ $examination->title }} · {{ $examination->subject?->name }}</p>
    </div>
    <a href="{{ route('teacher.exams.index') }}"
      class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">←
      Kembali</a>
  </div>

  {{-- Stats Cards --}}
  <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 text-center">
      <p class="text-2xl font-bold text-gray-900">{{ $stats->total }}</p>
      <p class="text-xs text-gray-500">Total Siswa</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
      <p class="text-2xl font-bold text-yellow-600">{{ $stats->not_started }}</p>
      <p class="text-xs text-gray-500">Belum Mulai</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
      <p class="text-2xl font-bold text-blue-600">{{ $stats->in_progress }}</p>
      <p class="text-xs text-gray-500">Mengerjakan</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
      <p class="text-2xl font-bold text-green-600">{{ $stats->completed }}</p>
      <p class="text-xs text-gray-500">Selesai</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
      <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats->avg_score, 1) }}</p>
      <p class="text-xs text-gray-500">Rata-rata ({{ $stats->passed }} lulus)</p>
    </div>
  </div>

  {{-- Progress Bar --}}
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-sm font-medium text-gray-700">Progress Keseluruhan</span>
      <span class="text-sm text-gray-500">{{ $stats->completed }}/{{ $stats->total }}</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-3">
      <div class="h-3 rounded-full transition-all duration-500"
        style="width: {{ $stats->total > 0 ? ($stats->completed / $stats->total) * 100 : 0 }}%; background: linear-gradient(to right, #10b981, #059669);">
      </div>
    </div>
  </div>

  {{-- Student Table --}}
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggaran</th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @foreach($monitorData as $m)
          <tr
            class="{{ $m->status === 'mengerjakan' ? 'bg-blue-50' : ($m->status === 'perlu_dinilai' ? 'bg-orange-50' : '') }}">
            <td class="px-4 py-3">
              <p class="text-sm font-medium text-gray-900">{{ $m->student->user->name }}</p>
              <p class="text-xs text-gray-500">{{ $m->student->nis }}</p>
            </td>
            <td class="px-4 py-3">
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                {{ $m->status === 'selesai' ? 'bg-green-100 text-green-800' : ($m->status === 'perlu_dinilai' ? 'bg-orange-100 text-orange-800' : ($m->status === 'mengerjakan' ? 'bg-blue-100 text-blue-800 animate-pulse' : 'bg-gray-100 text-gray-600')) }}">
                {{ $m->status === 'selesai' ? '✓ Selesai' : ($m->status === 'perlu_dinilai' ? '✏️ Perlu Dinilai' : ($m->status === 'mengerjakan' ? '⏳ Mengerjakan' : '— Belum Mulai')) }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="flex-1 bg-gray-200 rounded-full h-2 w-24">
                  <div class="h-2 rounded-full transition-all {{ $m->progress === 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                    style="width: {{ $m->progress }}%"></div>
                </div>
                <span class="text-xs text-gray-500">{{ $m->answered }}/{{ $m->total }}</span>
              </div>
            </td>
            <td class="px-4 py-3">
              @if($m->status === 'selesai')
                <span
                  class="text-sm font-bold {{ $m->is_passed ? 'text-green-600' : 'text-red-600' }}">{{ $m->score }}</span>
              @else
                <span class="text-sm text-gray-400">—</span>
              @endif
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">
              @if($m->started_at){{ $m->started_at->format('H:i') }}@endif
              @if($m->finished_at) → {{ $m->finished_at->format('H:i') }}@endif
            </td>
            <td class="px-4 py-3">
              @if($m->violations > 0)
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800">{{ $m->violations }}x</span>
              @else
                <span class="text-xs text-gray-400">0</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              @if($m->status === 'perlu_dinilai' && $m->attempt)
                <a href="{{ route('teacher.exams.grade', [$examination, $m->attempt]) }}"
                  class="inline-flex items-center rounded-md bg-orange-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-orange-500">
                  ✏️ Nilai Essay
                </a>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <p class="text-xs text-gray-400 mt-3 text-center">Auto-refresh setiap 5 detik</p>
</div>