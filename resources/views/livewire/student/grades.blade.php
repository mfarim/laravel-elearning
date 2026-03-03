<div class="pb-20">
  {{-- Summary Cards --}}
  <div class="grid grid-cols-3 gap-3 mb-4">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 text-center text-white">
      <p class="text-2xl font-bold">{{ $overallAvg }}</p>
      <p class="text-xs opacity-80">Rata-rata</p>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-3 text-center text-white">
      <p class="text-2xl font-bold">{{ $totalPassed }}</p>
      <p class="text-xs opacity-80">Lulus</p>
    </div>
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 text-center text-white">
      <p class="text-2xl font-bold">{{ $totalExams }}</p>
      <p class="text-xs opacity-80">Total Ujian</p>
    </div>
  </div>

  {{-- Overall Progress --}}
  <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-medium text-gray-700">Progress Keseluruhan</span>
      <span
        class="text-sm font-bold {{ $overallAvg >= 75 ? 'text-green-600' : ($overallAvg >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
        {{ $overallAvg >= 75 ? '✅ Baik' : ($overallAvg >= 60 ? '⚠️ Cukup' : '❌ Perlu Perbaikan') }}
      </span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-3">
      <div
        class="h-3 rounded-full transition-all duration-500 {{ $overallAvg >= 75 ? 'bg-green-500' : ($overallAvg >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
        style="width: {{ min(100, $overallAvg) }}%"></div>
    </div>
  </div>

  {{-- Per Subject Cards --}}
  <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Nilai Per Mata Pelajaran</h3>
  <div class="space-y-3">
    @forelse($gradesData as $g)
      <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h4 class="text-sm font-bold text-gray-900">{{ $g->subject->name }}</h4>
            <p class="text-xs text-gray-500">{{ $g->subject->code }}</p>
          </div>
          <div class="text-right">
            <p
              class="text-2xl font-bold {{ $g->overall >= 75 ? 'text-green-600' : ($g->overall >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
              {{ $g->overall }}
            </p>
            <p class="text-xs text-gray-400">Rata-rata</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-blue-50 rounded-lg p-2.5">
            <div class="flex items-center justify-between mb-1">
              <span class="text-xs text-blue-700 font-medium">📝 Ujian</span>
              <span class="text-xs text-blue-600">{{ $g->passed_exams }}/{{ $g->total_exams }} lulus</span>
            </div>
            <p class="text-lg font-bold text-blue-800">{{ $g->avg_exam }}</p>
          </div>
          <div class="bg-orange-50 rounded-lg p-2.5">
            <div class="flex items-center justify-between mb-1">
              <span class="text-xs text-orange-700 font-medium">📋 Tugas</span>
              <span class="text-xs text-orange-600">{{ $g->total_assignments }} dinilai</span>
            </div>
            <p class="text-lg font-bold text-orange-800">{{ $g->avg_assignment }}</p>
          </div>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <p class="text-3xl mb-2">📊</p>
        <p class="text-sm text-gray-500">Belum ada data nilai</p>
      </div>
    @endforelse
  </div>
</div>