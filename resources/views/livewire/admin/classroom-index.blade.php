<div>
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Manajemen Kelas</h2>
    </div>
    <button wire:click="create"
      class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">+
      Tambah Kelas</button>
  </div>
  <div class="mb-4"><input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kelas..."
      class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
  </div>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kelas</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tingkat</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Wali Kelas</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tahun</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($classrooms as $c)
          <tr>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $c->name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $c->level }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $c->homeroomTeacher?->user?->name ?? '-' }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $c->students_count }}/{{ $c->capacity }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $c->academic_year }}</td>
            <td class="px-6 py-4 text-right text-sm">
              <button wire:click="edit({{ $c->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
              <button wire:click="confirmDelete({{ $c->id }})" class="text-red-600 hover:text-red-900">Hapus</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada data kelas</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-6 py-3 border-t">{{ $classrooms->links() }}</div>
  </div>

  @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showForm', false)"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:w-full sm:max-w-lg">
          <form wire:submit="save">
            <div class="p-6">
              <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit Kelas' : 'Tambah Kelas' }}</h3>
              <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Nama Kelas</label><input wire:model="name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">@error('name')<span
                    class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                <div class="grid grid-cols-2 gap-4">
                  <div><label class="block text-sm font-medium text-gray-700">Tingkat</label><input wire:model="level"
                      type="number" min="1" max="12"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  </div>
                  <div><label class="block text-sm font-medium text-gray-700">Kapasitas</label><input
                      wire:model="capacity" type="number" min="1"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  </div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Wali Kelas</label><select
                    wire:model="homeroom_teacher_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">- Pilih Guru -</option>@foreach($teachers as $t)<option value="{{ $t->id }}">
                    {{ $t->user->name }}</option>@endforeach
                  </select></div>
                <div><label class="block text-sm font-medium text-gray-700">Tahun Akademik</label><input
                    wire:model="academic_year" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button type="submit"
                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Simpan</button>
              <button type="button" wire:click="$set('showForm', false)"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Batal</button>
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
          <p class="text-sm text-gray-500 mb-4">Yakin ingin menghapus kelas ini?</p>
          <div class="flex justify-center gap-3">
            <button wire:click="$set('showDeleteModal', false)"
              class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50">Batal</button>
            <button wire:click="delete"
              class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus</button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>