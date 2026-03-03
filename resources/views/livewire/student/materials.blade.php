<div class="space-y-4">
  <h2 class="text-lg font-bold text-gray-900">📚 Materi Pembelajaran</h2>
  <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari materi..."
    class="block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 text-sm">

  @forelse($materials as $m)
    <a href="/student/materials/{{ $m->id }}"
      class="block bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md hover:border-blue-200 transition-all">
      <div class="flex items-start gap-3">
        <div
          class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-lg
                      {{ $m->type === 'video' ? 'bg-red-100' : ($m->type === 'audio' ? 'bg-purple-100' : ($m->type === 'link' ? 'bg-blue-100' : 'bg-emerald-100')) }}">
          {{ $m->type === 'video' ? '🎬' : ($m->type === 'audio' ? '🎵' : ($m->type === 'link' ? '🔗' : ($m->type === 'text' ? '📝' : '📄'))) }}
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="font-semibold text-gray-900 text-sm">{{ $m->title }}</h3>
          <p class="text-xs text-gray-500 mt-0.5">{{ $m->subject?->name }} · {{ ucfirst($m->type) }}</p>
          @if($m->description)
          <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $m->description }}</p>@endif
          <span class="inline-flex items-center mt-2 text-xs text-blue-600 font-medium">Lihat Detail →</span>
        </div>
      </div>
    </a>
  @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-sm text-gray-400">Belum ada
      materi</div>
  @endforelse

  <div>{{ $materials->links() }}</div>
</div>