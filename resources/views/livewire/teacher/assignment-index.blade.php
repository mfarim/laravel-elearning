<div>
  {{-- Toast Notification --}}
  @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
      x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-[-1rem]"
      x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-[-1rem]"
      class="fixed top-4 right-4 z-[999] flex items-center gap-2 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      {{ session('success') }}
    </div>
  @endif
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Penugasan</h2>
    </div>
    <button wire:click="create"
      class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">+
      Tambah Tugas</button>
  </div>
  <div class="mb-4"><input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari tugas..."
      class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-emerald-600 sm:text-sm">
  </div>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mapel</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submissions</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($assignments as $a)
          <tr>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $a->title }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $a->subject?->name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $a->classroom?->name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $a->due_date->format('d M Y H:i') }}</td>
            <td class="px-6 py-4 text-sm"><a href="/teacher/assignments/{{ $a->id }}/submissions"
                class="text-emerald-600 hover:text-emerald-800 font-semibold hover:underline">{{ $a->submissions_count }}
                📋</a></td>
            <td class="px-6 py-4 text-sm"><span
                class="inline-flex rounded-full px-2 text-xs font-semibold {{ $a->status === 'published' ? 'bg-green-100 text-green-800' : ($a->status === 'closed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($a->status) }}</span>
            </td>
            <td class="px-6 py-4 text-right text-sm">
              <button wire:click="edit({{ $a->id }})" class="text-emerald-600 hover:text-emerald-900 mr-3">Edit</button>
              <button wire:click="confirmDelete({{ $a->id }})" class="text-red-600 hover:text-red-900">Hapus</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada tugas</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-6 py-3 border-t">{{ $assignments->links() }}</div>
  </div>
  @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showForm', false)"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:w-full sm:max-w-lg max-h-[90vh] overflow-y-auto">
          <form wire:submit="save">
            <div class="p-6">
              <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit' : 'Tambah' }} Tugas</h3>
              <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Judul</label><input wire:model="title"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">@error('title')<span
                    class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                <div class="grid grid-cols-2 gap-4">
                  <div><label class="block text-sm font-medium text-gray-700">Mapel</label><select wire:model="subject_id"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                      <option value="">Pilih</option>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}
                      </option>@endforeach
                    </select>@error('subject_id')<span class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                  <div><label class="block text-sm font-medium text-gray-700">Kelas</label><select
                      wire:model="classroom_id"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                      <option value="">Pilih</option>@foreach($classrooms as $c)<option value="{{ $c->id }}">
                        {{ $c->name }}
                      </option>@endforeach
                    </select>@error('classroom_id')<span class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Deskripsi</label><textarea
                    wire:model="description" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"></textarea>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Instruksi</label><textarea
                    wire:model="instructions" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"></textarea>
                </div>
                <div class="grid grid-cols-3 gap-4">
                  <div><label class="block text-sm font-medium text-gray-700">Nilai Max</label><input
                      wire:model="max_score" type="number" min="1"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                  </div>
                  <div><label class="block text-sm font-medium text-gray-700">Deadline</label><input wire:model="due_date"
                      type="datetime-local"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">@error('due_date')<span
                      class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                  <div><label class="block text-sm font-medium text-gray-700">Status</label><select wire:model="status"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                      <option value="draft">Draft</option>
                      <option value="published">Published</option>
                      <option value="closed">Closed</option>
                    </select></div>
                </div>
                <div><label class="flex items-center"><input wire:model="allow_late_submission" type="checkbox"
                      class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"><span
                      class="ml-2 text-sm text-gray-700">Izinkan terlambat</span></label></div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button type="submit"
                class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500 sm:ml-3 sm:w-auto">Simpan</button>
              <button type="button" wire:click="$set('showForm', false)"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 sm:mt-0 sm:w-auto">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
  @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:max-w-sm p-6 text-center">
          <p class="text-sm text-gray-500 mb-4">Yakin ingin menghapus tugas ini?</p>
          <div class="flex justify-center gap-3"><button wire:click="$set('showDeleteModal', false)"
              class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300">Batal</button><button
              wire:click="delete"
              class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus</button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>