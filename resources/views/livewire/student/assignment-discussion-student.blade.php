<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <h2 class="text-lg font-bold text-gray-900">💬 Diskusi</h2>
    <a href="/student/assignments" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <h3 class="font-semibold text-gray-900 text-sm">{{ $assignment->title }}</h3>
    <p class="text-xs text-gray-500 mt-0.5">{{ $assignment->subject?->name }} · Deadline:
      {{ $assignment->due_date->format('d M Y H:i') }} · Nilai Max: {{ $assignment->max_score }}
    </p>
    @if($assignment->description)
      <div class="mt-3 bg-gray-50 rounded-lg p-3">
        <p class="text-xs font-semibold text-gray-600 mb-1">📄 Deskripsi</p>
        <p class="text-sm text-gray-700 leading-relaxed">{{ $assignment->description }}</p>
      </div>
    @endif
    @if($assignment->instructions)
      <div class="mt-2 bg-blue-50 rounded-lg p-3">
        <p class="text-xs font-semibold text-blue-700 mb-1">📋 Instruksi Pengerjaan</p>
        <p class="text-sm text-blue-800 leading-relaxed">{{ $assignment->instructions }}</p>
      </div>
    @endif
  </div>

  {{-- Send message --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    @if($replyingTo)
      @php $replyMsg = $discussions->firstWhere('id', $replyingTo); @endphp
      <div class="flex items-center gap-2 mb-3 bg-blue-50 rounded-lg px-3 py-2 text-sm text-blue-700">
        <span>Membalas: <strong>{{ $replyMsg?->user?->name }}</strong></span>
        <button wire:click="cancelReply" class="ml-auto text-blue-500 hover:text-blue-700">&times;</button>
      </div>
    @endif
    <form wire:submit="send" class="flex gap-2">
      <input wire:model="message" type="text" placeholder="Tulis pesan..."
        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
      <button type="submit"
        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
        Kirim
      </button>
    </form>
    @error('message')<span class="text-sm text-red-600 mt-1">{{ $message }}</span>@enderror
  </div>

  {{-- Messages --}}
  @forelse($discussions as $d)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white
                    {{ $d->user?->hasRole('guru') ? 'bg-emerald-500' : 'bg-blue-500' }}">
            {{ strtoupper(substr($d->user?->name ?? '?', 0, 1)) }}
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">{{ $d->user?->name }}
              @if($d->user?->hasRole('guru'))
                <span
                  class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-700 ml-1">Guru</span>
              @endif
            </p>
            <p class="text-[10px] text-gray-400">{{ $d->created_at->diffForHumans() }}</p>
          </div>
        </div>
        <button wire:click="reply({{ $d->id }})" class="text-xs text-gray-400 hover:text-blue-600">Balas</button>
      </div>
      <p class="mt-2 text-sm text-gray-700 leading-relaxed">{{ $d->message }}</p>

      {{-- Replies --}}
      @if($d->replies->count())
        <div class="mt-3 ml-6 space-y-3 border-l-2 border-gray-100 pl-4">
          @foreach($d->replies as $r)
            <div>
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white
                                          {{ $r->user?->hasRole('guru') ? 'bg-emerald-500' : 'bg-blue-500' }}">
                  {{ strtoupper(substr($r->user?->name ?? '?', 0, 1)) }}
                </div>
                <span class="text-xs font-semibold text-gray-800">{{ $r->user?->name }}</span>
                @if($r->user?->hasRole('guru'))
                  <span
                    class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-700">Guru</span>
                @endif
                <span class="text-[10px] text-gray-400">{{ $r->created_at->diffForHumans() }}</span>
              </div>
              <p class="mt-1 text-sm text-gray-600">{{ $r->message }}</p>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-sm text-gray-400">
      Belum ada diskusi. Mulai percakapan pertama! 💬
    </div>
  @endforelse
</div>