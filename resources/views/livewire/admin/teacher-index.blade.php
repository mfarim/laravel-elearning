<div>
  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Manajemen Guru</h2>
      <p class="mt-1 text-sm text-gray-500">Kelola data guru dan akun login.</p>
    </div>
    <button wire:click="create"
      class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
      + Tambah Guru
    </button>
  </div>

  {{-- Search --}}
  <div class="mb-4">
    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau NIP..."
      class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
  </div>

  {{-- Table --}}
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($teachers as $teacher)
          <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $teacher->user->name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $teacher->nip }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $teacher->user->email }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $teacher->user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $teacher->user->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
              <a href="/admin/impersonate/{{ $teacher->user_id }}" class="text-amber-600 hover:text-amber-900 mr-3"
                title="Login sebagai guru ini">👤 Login</a>
              <button wire:click="edit({{ $teacher->id }})"
                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
              <button wire:click="confirmDelete({{ $teacher->id }})"
                class="text-red-600 hover:text-red-900">Hapus</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada data guru</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-6 py-3 border-t">{{ $teachers->links() }}</div>
  </div>

  {{-- Create/Edit Modal --}}
  @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showForm', false)"></div>
        <div
          class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">
          <form wire:submit="save">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
              <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit Guru' : 'Tambah Guru' }}</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Nama</label>
                  <input wire:model="name" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">NIP</label>
                  <input wire:model="nip" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  @error('nip') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Email</label>
                  <input wire:model="email" type="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Password
                    {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}</label>
                  <input wire:model="password" type="password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Telepon</label>
                  <input wire:model="phone" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Alamat</label>
                  <textarea wire:model="address" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button type="submit"
                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Simpan</button>
              <button type="button" wire:click="$set('showForm', false)"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  {{-- Delete Confirmation Modal --}}
  @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div
          class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-sm">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 text-center">
            <p class="text-sm text-gray-500">Yakin ingin menghapus data guru ini?</p>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <button wire:click="delete"
              class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Hapus</button>
            <button wire:click="$set('showDeleteModal', false)"
              class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Batal</button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>