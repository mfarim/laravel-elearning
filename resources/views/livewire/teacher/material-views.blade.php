<div>
  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <a href="/teacher/materials" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">← Kembali ke
        Materi</a>
      <h2 class="text-xl font-bold text-gray-900 mt-1">👁 Dilihat oleh Siswa</h2>
      <p class="text-sm text-gray-500">{{ $material->title }} · {{ $material->subject?->name }} ·
        {{ $material->classroom?->name ?? 'Semua Kelas' }}</p>
    </div>
    <div class="mt-3 sm:mt-0">
      @php $viewedCount = count(array_filter($viewedStudentIds)); @endphp
      <span class="inline-flex items-center rounded-md bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">
        {{ $viewedCount }}/{{ $students->count() }} Sudah Membaca
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
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Dilihat</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($students as $i => $s)
          @php $viewedAt = $viewedStudentIds[$s->id] ?? null; @endphp
          <tr>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $i + 1 }}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $s->user?->name ?? $s->name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $s->nis }}</td>
            <td class="px-6 py-4 text-sm">
              @if($viewedAt)
                <span class="inline-flex rounded-full px-2 text-xs font-semibold bg-green-100 text-green-800">✅ Sudah</span>
              @else
                <span class="inline-flex rounded-full px-2 text-xs font-semibold bg-gray-100 text-gray-500">Belum</span>
              @endif
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              {{ $viewedAt ? \Carbon\Carbon::parse($viewedAt)->format('d M Y H:i') : '-' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada siswa</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>