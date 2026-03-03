<div>
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Manajemen Siswa</h2>
      <p class="mt-1 text-sm text-gray-500">Kelola data siswa dan akun login.</p>
    </div>
    <div class="flex items-center gap-2 mt-3 sm:mt-0">
      <button wire:click="$set('showImport', true)"
        class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">📥
        Import Excel</button>
      <button wire:click="create"
        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">+
        Tambah Siswa</button>
    </div>
  </div>

  <div class="flex gap-4 mb-4">
    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau NIS..."
      class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
    <select wire:model.live="filterClassroom"
      class="rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
      <option value="">Semua Kelas</option>
      @foreach($classrooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
    </select>
  </div>

  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIS</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">JK</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($students as $student)
          <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $student->user->name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->nis }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->classroom?->name ?? '-' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->gender }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
              <a href="/admin/impersonate/{{ $student->user_id }}" class="text-amber-600 hover:text-amber-900 mr-3"
                title="Login sebagai siswa ini">👤 Login</a>
              <button wire:click="edit({{ $student->id }})"
                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
              <button wire:click="confirmDelete({{ $student->id }})"
                class="text-red-600 hover:text-red-900">Hapus</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada data siswa</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-6 py-3 border-t">{{ $students->links() }}</div>
  </div>

  @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
      <div class="flex min-h-full items-end justify-center p-4 sm:items-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showForm', false)"></div>
        <div
          class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">
          <form wire:submit="save">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
              <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit Siswa' : 'Tambah Siswa' }}</h3>
              <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Nama</label><input wire:model="name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">@error('name')<span
                    class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                <div class="grid grid-cols-2 gap-4">
                  <div><label class="block text-sm font-medium text-gray-700">NIS</label><input wire:model="nis"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">@error('nis')<span
                      class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                  <div><label class="block text-sm font-medium text-gray-700">NISN</label><input wire:model="nisn"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  </div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Email</label><input wire:model="email"
                    type="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">@error('email')<span
                    class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                <div><label class="block text-sm font-medium text-gray-700">Password
                    {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}</label><input wire:model="password"
                    type="password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">@error('password')<span
                    class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                <div class="grid grid-cols-2 gap-4">
                  <div><label class="block text-sm font-medium text-gray-700">Kelas</label><select
                      wire:model="classroom_id"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                      <option value="">Pilih Kelas</option>@foreach($classrooms as $c)<option value="{{ $c->id }}">
                        {{ $c->name }}
                      </option>@endforeach
                    </select>@error('classroom_id')<span class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                  <div><label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label><select
                      wire:model="gender"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                      <option value="L">Laki-laki</option>
                      <option value="P">Perempuan</option>
                    </select></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label><input
                    wire:model="birth_date" type="date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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

  @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:w-full sm:max-w-sm p-6 text-center">
          <p class="text-sm text-gray-500 mb-4">Yakin ingin menghapus siswa ini?</p>
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

  @if($showImport)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showImport', false)"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:w-full sm:max-w-md">
          <form wire:submit="importExcel">
            <div class="p-6">
              <h3 class="text-lg font-semibold mb-4">📥 Import Siswa dari Excel</h3>
              <div class="bg-blue-50 rounded-lg p-3 mb-4 text-xs text-blue-800">
                <p class="font-semibold mb-1">Format kolom yang dibutuhkan:</p>
                <p>nama, email, nis, nisn, kelas, gender (L/P), tanggal_lahir, alamat, password</p>
                <p class="mt-1 text-blue-600">* Kolom wajib: nama, email, nis, kelas</p>
              </div>
              <a href="/samples/import_siswa_sample.csv" download
                class="inline-flex items-center gap-2 mb-4 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg px-3 py-2 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Contoh File (.csv)
              </a>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel (.xlsx, .xls, .csv)</label>
                <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('importFile')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
              </div>
              <div wire:loading wire:target="importFile" class="mt-2 text-sm text-indigo-600">Mengupload file...</div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button type="submit" wire:loading.attr="disabled" wire:target="importExcel"
                class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500 sm:ml-3 sm:w-auto">
                <span wire:loading.remove wire:target="importExcel">Import</span>
                <span wire:loading wire:target="importExcel">Mengimport...</span>
              </button>
              <button type="button" wire:click="$set('showImport', false)"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 sm:mt-0 sm:w-auto">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>