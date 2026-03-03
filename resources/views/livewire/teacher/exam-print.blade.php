<div>
  {{-- Tab Selector --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6 no-print">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Print Dokumen Ujian</h2>
      <p class="text-sm text-gray-500 mt-1">{{ $exam->title }} · {{ $exam->subject?->name }}</p>
    </div>
    <div class="flex items-center gap-2 mt-3 sm:mt-0">
      <button wire:click="$set('printType', 'kartu')"
        class="rounded-md px-3 py-2 text-sm font-semibold {{ $printType === 'kartu' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700' }}">Kartu
        Ujian</button>
      <button wire:click="$set('printType', 'hadir')"
        class="rounded-md px-3 py-2 text-sm font-semibold {{ $printType === 'hadir' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700' }}">Daftar
        Hadir</button>
      <button wire:click="$set('printType', 'berita')"
        class="rounded-md px-3 py-2 text-sm font-semibold {{ $printType === 'berita' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700' }}">Berita
        Acara</button>
      <button onclick="window.print()"
        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-500 ml-2">🖨️
        Print</button>
    </div>
  </div>

  <style>
    @media print {
      .no-print {
        display: none !important;
      }

      .print-area {
        box-shadow: none !important;
      }
    }
  </style>

  <div class="bg-white shadow rounded-lg p-8 print-area" id="printArea">
    @if($printType === 'kartu')
      {{-- KARTU UJIAN --}}
      <div class="space-y-6">
        @foreach($students as $s)
          <div class="border-2 border-gray-300 rounded-lg p-4 page-break-inside-avoid">
            <div class="text-center border-b pb-3 mb-3">
              <h3 class="text-lg font-bold">KARTU PESERTA UJIAN</h3>
              <p class="text-sm text-gray-600">Laravel E-Learning System</p>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <div class="col-span-2 space-y-1 text-sm">
                <div class="flex"><span class="w-28 font-medium">Nama</span><span>: {{ $s->user->name }}</span></div>
                <div class="flex"><span class="w-28 font-medium">NIS</span><span>: {{ $s->nis }}</span></div>
                <div class="flex"><span class="w-28 font-medium">Kelas</span><span>: {{ $exam->classroom?->name }}</span>
                </div>
                <div class="flex"><span class="w-28 font-medium">Ujian</span><span>: {{ $exam->title }}</span></div>
                <div class="flex"><span class="w-28 font-medium">Mapel</span><span>: {{ $exam->subject?->name }}</span>
                </div>
                <div class="flex"><span class="w-28 font-medium">Tanggal</span><span>:
                    {{ $exam->start_at->format('d M Y') }}</span></div>
                <div class="flex"><span class="w-28 font-medium">Waktu</span><span>: {{ $exam->start_at->format('H:i') }} —
                    {{ $exam->end_at->format('H:i') }}</span></div>
                <div class="flex"><span class="w-28 font-medium">Durasi</span><span>: {{ $exam->duration_minutes }}
                    menit</span></div>
              </div>
              <div class="flex items-center justify-center">
                <div
                  class="w-24 h-28 border-2 border-dashed border-gray-300 flex items-center justify-center text-xs text-gray-400">
                  Foto 3x4</div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

    @elseif($printType === 'hadir')
      {{-- DAFTAR HADIR --}}
      <div class="text-center mb-6">
        <h3 class="text-lg font-bold mb-1">DAFTAR HADIR UJIAN</h3>
        <p class="text-sm text-gray-600">{{ $exam->title }} — {{ $exam->subject?->name }}</p>
        <p class="text-sm text-gray-600">{{ $exam->classroom?->name }} | {{ $exam->start_at->format('d M Y, H:i') }}</p>
      </div>
      <table class="w-full border-collapse border border-gray-400 text-sm">
        <thead>
          <tr class="bg-gray-100">
            <th class="border border-gray-400 px-3 py-2 w-10">No</th>
            <th class="border border-gray-400 px-3 py-2">Nama Siswa</th>
            <th class="border border-gray-400 px-3 py-2 w-24">NIS</th>
            <th class="border border-gray-400 px-3 py-2 w-32">Tanda Tangan</th>
            <th class="border border-gray-400 px-3 py-2 w-28">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          @foreach($students as $i => $s)
            <tr>
              <td class="border border-gray-400 px-3 py-3 text-center">{{ $i + 1 }}</td>
              <td class="border border-gray-400 px-3 py-3">{{ $s->user->name }}</td>
              <td class="border border-gray-400 px-3 py-3 text-center">{{ $s->nis }}</td>
              <td class="border border-gray-400 px-3 py-3"></td>
              <td class="border border-gray-400 px-3 py-3"></td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="mt-8 flex justify-end">
        <div class="text-center text-sm">
          <p>Pengawas Ujian,</p><br><br><br>
          <p class="border-b border-gray-400 w-40">_______________________</p>
          <p class="mt-1">NIP: _______________</p>
        </div>
      </div>

    @elseif($printType === 'berita')
      {{-- BERITA ACARA --}}
      <div class="text-center mb-6">
        <h3 class="text-lg font-bold mb-1">BERITA ACARA PELAKSANAAN UJIAN</h3>
        <p class="text-sm text-gray-600">Laravel E-Learning System</p>
      </div>
      <div class="space-y-3 text-sm leading-relaxed">
        <p>Pada hari ini, <strong>{{ $exam->start_at->locale('id')->translatedFormat('l, d F Y') }}</strong>, telah
          dilaksanakan ujian dengan rincian sebagai berikut:</p>
        <table class="w-full text-sm">
          <tr>
            <td class="w-40 py-1 font-medium">Nama Ujian</td>
            <td>: {{ $exam->title }}</td>
          </tr>
          <tr>
            <td class="w-40 py-1 font-medium">Tipe</td>
            <td>: {{ strtoupper($exam->type) }}</td>
          </tr>
          <tr>
            <td class="w-40 py-1 font-medium">Mata Pelajaran</td>
            <td>: {{ $exam->subject?->name }}</td>
          </tr>
          <tr>
            <td class="w-40 py-1 font-medium">Kelas</td>
            <td>: {{ $exam->classroom?->name }}</td>
          </tr>
          <tr>
            <td class="w-40 py-1 font-medium">Jumlah Peserta</td>
            <td>: {{ $students->count() }} siswa</td>
          </tr>
          <tr>
            <td class="w-40 py-1 font-medium">Jumlah Soal</td>
            <td>: {{ $exam->questions()->count() }} soal</td>
          </tr>
          <tr>
            <td class="w-40 py-1 font-medium">Durasi</td>
            <td>: {{ $exam->duration_minutes }} menit</td>
          </tr>
          <tr>
            <td class="w-40 py-1 font-medium">Waktu Pelaksanaan</td>
            <td>: {{ $exam->start_at->format('H:i') }} — {{ $exam->end_at->format('H:i') }}</td>
          </tr>
          <tr>
            <td class="w-40 py-1 font-medium">KKM</td>
            <td>: {{ $exam->passing_score }}</td>
          </tr>
        </table>
        <p class="mt-4">Catatan:</p>
        <div class="border border-gray-300 rounded p-3 min-h-[100px] text-gray-400">
          ______________________________________________________</div>
        <p class="mt-4">Demikian berita acara ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
      </div>
      <div class="mt-8 grid grid-cols-2 gap-8 text-sm text-center">
        <div>
          <p>Guru Pengampu,</p><br><br><br>
          <p class="border-b border-gray-400 mx-auto w-40">_______________________</p>
        </div>
        <div>
          <p>Pengawas Ujian,</p><br><br><br>
          <p class="border-b border-gray-400 mx-auto w-40">_______________________</p>
        </div>
      </div>
    @endif
  </div>
</div>