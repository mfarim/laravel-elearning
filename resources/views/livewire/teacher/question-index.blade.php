<div>
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Bank Soal</h2>
    </div>
    <button wire:click="create"
      class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">+
      Tambah Soal</button>
  </div>
  <div class="flex gap-4 mb-4">
    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari soal..."
      class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-emerald-600 sm:text-sm">
    <select wire:model.live="filterExam"
      class="rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-emerald-600 sm:text-sm min-w-[200px]">
      <option value="">Semua Ujian</option>
      @foreach($exams as $e)<option value="{{ $e->id }}">{{ $e->title }}</option>@endforeach
    </select>
  </div>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-8">#</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Soal</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ujian</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Difficulty</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Poin</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($questions as $i => $q)
          <tr>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $questions->firstItem() + $i }}</td>
            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
              {{ Str::limit(strip_tags($q->question_text), 80) }}</td>
            <td class="px-6 py-4 text-sm"><span
                class="inline-flex rounded-full px-2 text-xs font-semibold {{ $q->question_type === 'multiple_choice' ? 'bg-blue-100 text-blue-800' : ($q->question_type === 'essay' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800') }}">{{ $q->question_type === 'multiple_choice' ? 'PG' : ($q->question_type === 'essay' ? 'Essay' : 'Isian') }}</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $q->examination?->title }}</td>
            <td class="px-6 py-4 text-sm"><span
                class="inline-flex rounded-full px-2 text-xs font-semibold {{ $q->difficulty === 'easy' ? 'bg-green-100 text-green-800' : ($q->difficulty === 'hard' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($q->difficulty) }}</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ $q->points }}</td>
            <td class="px-6 py-4 text-right text-sm">
              <button wire:click="edit({{ $q->id }})" class="text-emerald-600 hover:text-emerald-900 mr-3">Edit</button>
              <button wire:click="confirmDelete({{ $q->id }})" class="text-red-600 hover:text-red-900">Hapus</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada soal</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-6 py-3 border-t">{{ $questions->links() }}</div>
  </div>

  @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showForm', false)"></div>
        <div class="relative rounded-lg bg-white shadow-xl sm:w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto">
          <form wire:submit="save">
            <div class="p-6">
              <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit' : 'Tambah' }} Soal</h3>
              <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                  <div><label class="block text-sm font-medium text-gray-700">Ujian</label><select
                      wire:model="examination_id"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                      <option value="">Pilih</option>@foreach($exams as $e)<option value="{{ $e->id }}">{{ $e->title }}
                      </option>@endforeach
                    </select>@error('examination_id')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                  </div>
                  <div><label class="block text-sm font-medium text-gray-700">Tipe</label><select
                      wire:model.live="question_type"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                      <option value="multiple_choice">Pilihan Ganda</option>
                      <option value="short_answer">Isian Singkat</option>
                      <option value="essay">Essay</option>
                    </select></div>
                  <div class="grid grid-cols-2 gap-2">
                    <div><label class="block text-sm font-medium text-gray-700">Poin</label><input wire:model="points"
                        type="number" min="1"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">Level</label><select
                        wire:model="difficulty"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                      </select></div>
                  </div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Pertanyaan</label><textarea
                    wire:model="question_text" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"></textarea>@error('question_text')<span
                    class="text-sm text-red-600">{{ $message }}</span>@enderror</div>
                @if($question_type === 'multiple_choice')
                  <div><label class="block text-sm font-medium text-gray-700 mb-2">Pilihan Jawaban (A-E)</label>
                    @foreach($options as $i => $opt)
                      <div class="flex items-center gap-2 mb-2"><span
                          class="text-sm font-bold text-gray-500 w-6">{{ chr(65 + $i) }}.</span><input
                          wire:model="options.{{ $i }}" type="text"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                          placeholder="Pilihan {{ chr(65 + $i) }}"></div>
                    @endforeach
                  </div>
                @endif
                <div><label class="block text-sm font-medium text-gray-700">Jawaban Benar
                    {{ $question_type === 'multiple_choice' ? '(A/B/C/D/E)' : '' }}</label><input
                    wire:model="correct_answer" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Pembahasan (opsional)</label><textarea
                    wire:model="explanation" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"></textarea>
                </div>
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
          <p class="text-sm text-gray-500 mb-4">Yakin ingin menghapus soal ini?</p>
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