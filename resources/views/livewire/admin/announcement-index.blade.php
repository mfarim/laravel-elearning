<div>
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Pengumuman</h2>
    </div>
    <button wire:click="create"
      class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">+
      Tambah Pengumuman</button>
  </div>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($announcements as $a)
          <tr>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $a->title }}</td>
            <td class="px-6 py-4 text-sm"><span
                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $a->target === 'all' ? 'bg-blue-100 text-blue-800' : ($a->target === 'teacher' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">{{ ucfirst($a->target) }}</span>
            </td>
            <td class="px-6 py-4 text-sm"><span
                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $a->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $a->is_published ? 'Published' : 'Draft' }}</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $a->created_at->format('d M Y') }}</td>
            <td class="px-6 py-4 text-right text-sm">
              <button wire:click="edit({{ $a->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
              <button wire:click="confirmDelete({{ $a->id }})" class="text-red-600 hover:text-red-900">Hapus</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada pengumuman</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-6 py-3 border-t">{{ $announcements->links() }}</div>
  </div>

  @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showForm', false)"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:w-full sm:max-w-lg">
          <form wire:submit="save">
            <div class="p-6">
              <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit' : 'Tambah' }} Pengumuman</h3>
              <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Judul</label><input wire:model="title"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">@error('title')<span
                    class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                <div><label class="block text-sm font-medium text-gray-700">Isi Pengumuman</label><textarea
                    wire:model="content" rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>@error('content')<span
                    class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                <div class="grid grid-cols-2 gap-4">
                  <div><label class="block text-sm font-medium text-gray-700">Target</label><select wire:model="target"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                      <option value="all">Semua</option>
                      <option value="teacher">Guru</option>
                      <option value="student">Siswa</option>
                    </select></div>
                  <div class="flex items-end"><label class="flex items-center"><input wire:model="is_published"
                        type="checkbox"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"><span
                        class="ml-2 text-sm text-gray-700">Publish langsung</span></label></div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button type="submit"
                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500 sm:ml-3 sm:w-auto">Simpan</button>
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
          <p class="text-sm text-gray-500 mb-4">Yakin ingin menghapus pengumuman ini?</p>
          <div class="flex justify-center gap-3">
            <button wire:click="$set('showDeleteModal', false)"
              class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300">Batal</button>
            <button wire:click="delete"
              class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus</button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>