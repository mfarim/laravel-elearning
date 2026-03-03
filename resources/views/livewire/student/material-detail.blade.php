<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <h2 class="text-lg font-bold text-gray-900">📚 Detail Materi</h2>
    <a href="/student/materials" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
  </div>

  {{-- Material Info --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-start gap-3">
      <div
        class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center text-xl
        {{ $material->type === 'video' ? 'bg-red-100' : ($material->type === 'audio' ? 'bg-purple-100' : ($material->type === 'link' ? 'bg-blue-100' : 'bg-emerald-100')) }}">
        {{ $material->type === 'video' ? '🎬' : ($material->type === 'audio' ? '🎵' : ($material->type === 'link' ? '🔗' : ($material->type === 'text' ? '📝' : '📄'))) }}
      </div>
      <div class="flex-1 min-w-0">
        <h3 class="font-bold text-gray-900">{{ $material->title }}</h3>
        <p class="text-xs text-gray-500 mt-0.5">
          {{ $material->subject?->name }}
          · {{ ucfirst($material->type) }}
          · Oleh: {{ $material->teacher?->user?->name ?? '-' }}
          · {{ $material->created_at->format('d M Y') }}
        </p>
      </div>
    </div>
    @if($material->description)
      <div class="mt-3 bg-gray-50 rounded-lg p-3">
        <p class="text-sm text-gray-700 leading-relaxed">{{ $material->description }}</p>
      </div>
    @endif
  </div>

  {{-- Content Preview --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @if($material->type === 'video' && $youtubeId)
      {{-- YouTube Embed --}}
      <div class="aspect-video">
        <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0" class="w-full h-full" frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen></iframe>
      </div>
    @elseif($material->type === 'video' && $material->file_url)
      {{-- Non-YouTube video link --}}
      <div class="p-6 text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center text-3xl mx-auto mb-3">🎬</div>
        <p class="text-sm text-gray-600 mb-3">Video tersedia di link eksternal</p>
        <a href="{{ $material->file_url }}" target="_blank" rel="noopener"
          class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">
          ▶ Tonton Video
        </a>
      </div>
    @elseif($material->type === 'document' && $material->file_path)
      @php
        $ext = pathinfo($material->file_path, PATHINFO_EXTENSION);
        $fileUrl = Storage::url($material->file_path);
      @endphp
      @if(strtolower($ext) === 'pdf')
        {{-- PDF Embed --}}
        <div class="p-3 bg-gray-50 border-b flex items-center justify-between">
          <span class="text-sm font-medium text-gray-700">📄 Preview PDF</span>
          <a href="{{ $fileUrl }}" download class="text-xs text-blue-600 font-medium hover:text-blue-800">⬇ Download</a>
        </div>
        <iframe src="{{ $fileUrl }}#toolbar=1&navpanes=0" class="w-full" style="height: 600px;" frameborder="0"></iframe>
      @else
        {{-- Other document types --}}
        <div class="p-6 text-center">
          <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center text-3xl mx-auto mb-3">📄</div>
          <p class="text-sm text-gray-600 mb-1">{{ strtoupper($ext) }} Document</p>
          <p class="text-xs text-gray-400 mb-4">{{ $material->title }}.{{ $ext }}</p>
          <a href="{{ $fileUrl }}" download
            class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
            ⬇ Download File
          </a>
        </div>
      @endif
    @elseif($material->type === 'text' && $material->content)
      {{-- Text Content --}}
      <div class="p-5">
        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
          {!! nl2br(e($material->content)) !!}
        </div>
      </div>
    @elseif($material->type === 'audio' && $material->file_path)
      {{-- Audio Player --}}
      <div class="p-6 text-center">
        <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center text-3xl mx-auto mb-4">🎵</div>
        <audio controls class="w-full max-w-md mx-auto">
          <source src="{{ Storage::url($material->file_path) }}" type="audio/mpeg">
          Browser tidak mendukung audio player.
        </audio>
      </div>
    @elseif($material->type === 'audio' && $material->file_url)
      <div class="p-6 text-center">
        <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center text-3xl mx-auto mb-4">🎵</div>
        <audio controls class="w-full max-w-md mx-auto">
          <source src="{{ $material->file_url }}">
          Browser tidak mendukung audio player.
        </audio>
      </div>
    @elseif($material->type === 'link' && $material->file_url)
      {{-- Link --}}
      <div class="p-6 text-center">
        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-3xl mx-auto mb-3">🔗</div>
        <p class="text-sm text-gray-600 mb-1">Link Eksternal</p>
        <p class="text-xs text-gray-400 mb-4 truncate px-4">{{ $material->file_url }}</p>
        <a href="{{ $material->file_url }}" target="_blank" rel="noopener"
          class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">
          🔗 Buka Link
        </a>
      </div>
    @else
      {{-- Fallback --}}
      <div class="p-8 text-center text-sm text-gray-400">
        Konten tidak tersedia untuk materi ini.
      </div>
    @endif
  </div>

  {{-- Download area for materials with file --}}
  @if($material->file_path && $material->type !== 'document')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600">📎 File tersedia untuk diunduh</span>
      </div>
      <a href="{{ Storage::url($material->file_path) }}" download
        class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200">
        ⬇ Download
      </a>
    </div>
  @endif
</div>