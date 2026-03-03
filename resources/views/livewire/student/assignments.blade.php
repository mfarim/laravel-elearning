<div class="space-y-4">
  <h2 class="text-lg font-bold text-gray-900">📋 Tugas</h2>

  @forelse($assignments as $a)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
      <div class="flex items-start justify-between">
        <div>
          <h3 class="font-semibold text-gray-900 text-sm">{{ $a->title }}</h3>
          <p class="text-xs text-gray-500 mt-0.5">{{ $a->subject?->name }}</p>
        </div>
        @if(in_array($a->id, $submittedIds))
          <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800">✓
            Dikumpulkan</span>
        @else
          <span
            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-800">Belum</span>
        @endif
      </div>
      @if($a->description)
      <p class="text-xs text-gray-400 mt-2 line-clamp-2">{{ $a->description }}</p>@endif
      <div class="mt-3 flex items-center justify-between">
        <span class="text-xs {{ $a->due_date->isPast() ? 'text-red-600' : 'text-gray-500' }}">
          ⏰ {{ $a->due_date->format('d M Y H:i') }}{{ $a->due_date->isPast() ? ' (Lewat)' : '' }}
        </span>
        <div class="flex items-center gap-2">
          <a href="/student/assignments/{{ $a->id }}/discussion"
            class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200">💬</a>
          @if(!in_array($a->id, $submittedIds) && (!$a->due_date->isPast() || $a->allow_late_submission))
            <button wire:click="openSubmit({{ $a->id }})"
              class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-500">Kumpulkan</button>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-sm text-gray-400">Tidak ada
      tugas 🎉</div>
  @endforelse

  <div>{{ $assignments->links() }}</div>

  @if($showSubmit)
    <div class="fixed inset-0 z-[60] overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4 pb-24">
        <div class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm" wire:click="$set('showSubmit', false)"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl p-6">
          <h3 class="text-lg font-semibold mb-4">Kumpulkan Tugas</h3>
          <form wire:submit="submit" class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Upload File</label>
              <input wire:model="submission_file" type="file"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
              @error('submission_file')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
              <textarea wire:model="notes" rows="2"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
            </div>
            <div class="flex gap-3">
              <button type="button" wire:click="$set('showSubmit', false)"
                class="flex-1 rounded-lg bg-gray-100 px-3 py-2.5 text-sm font-semibold text-gray-700">Batal</button>
              <button type="submit"
                class="flex-1 rounded-lg bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Kirim</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>