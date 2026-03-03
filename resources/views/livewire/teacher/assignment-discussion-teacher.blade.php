<div>
  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <a href="/teacher/assignments/{{ $assignment->id }}/submissions"
        class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">← Kembali ke Submissions</a>
      <h2 class="text-xl font-bold text-gray-900 mt-1">💬 Forum Diskusi</h2>
      <p class="text-sm text-gray-500">{{ $assignment->title }} · {{ $assignment->subject?->name }} ·
        {{ $assignment->classroom?->name }}
      </p>
    </div>
  </div>

  {{-- Send message --}}
  <div class="bg-white shadow rounded-lg p-4 mb-6">
    @if($replyingTo)
      @php $replyMsg = $discussions->firstWhere('id', $replyingTo); @endphp
      <div class="flex items-center gap-2 mb-3 bg-blue-50 rounded-lg px-3 py-2 text-sm text-blue-700">
        <span>Membalas: <strong>{{ $replyMsg?->user?->name }}</strong></span>
        <button wire:click="cancelReply" class="ml-auto text-blue-500 hover:text-blue-700">&times;</button>
      </div>
    @endif
    <form wire:submit="send" class="flex gap-3">
      <input wire:model="message" type="text" placeholder="Tulis pesan..."
        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
      <button type="submit"
        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 shadow-sm">
        Kirim
      </button>
    </form>
    @error('message')<span class="text-sm text-red-600 mt-1">{{ $message }}</span>@enderror
  </div>

  {{-- Messages --}}
  <div class="space-y-4">
    @forelse($discussions as $d)
      <div class="bg-white shadow rounded-lg p-4">
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
                @else
                  <span
                    class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-semibold bg-blue-100 text-blue-700 ml-1">Siswa</span>
                @endif
              </p>
              <p class="text-[10px] text-gray-400">{{ $d->created_at->diffForHumans() }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button wire:click="reply({{ $d->id }})" class="text-xs text-gray-400 hover:text-blue-600">Balas</button>
            <button wire:click="confirmDeleteMessage({{ $d->id }})"
              class="text-xs text-gray-400 hover:text-red-600">Hapus</button>
          </div>
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
                  <button wire:click="confirmDeleteMessage({{ $r->id }})"
                    class="text-[10px] text-gray-300 hover:text-red-500 ml-1">Hapus</button>
                </div>
                <p class="mt-1 text-sm text-gray-600">{{ $r->message }}</p>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    @empty
      <div class="bg-white shadow rounded-lg p-8 text-center text-sm text-gray-400">
        Belum ada diskusi. Mulai percakapan pertama! 💬
      </div>
    @endforelse
  </div>

  {{-- Delete Confirmation Modal --}}
  @if($deletingMessageId)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:max-w-sm p-6 text-center">
          <p class="text-sm text-gray-500 mb-4">Yakin ingin menghapus pesan ini?</p>
          <div class="flex justify-center gap-3">
            <button wire:click="$set('deletingMessageId', null)"
              class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300">Batal</button>
            <button wire:click="deleteMessage"
              class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus</button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>