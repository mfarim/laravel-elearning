@php
  $currentRoute = request()->route()?->getName() ?? '';
@endphp

<div class="flex grow flex-col overflow-y-auto bg-gradient-to-b from-[#059669] to-[#065f46]">
  {{-- Logo --}}
  <div class="flex h-16 items-center gap-3 px-5 border-b border-white/10">
    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-white/20">
      <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
      </svg>
    </div>
    <span class="text-lg font-bold text-white tracking-tight">Laravel E-Learning</span>
  </div>

  <nav class="flex-1 px-3 py-4 space-y-6">
    {{-- Dashboard --}}
    <div>
      <a href="{{ route('teacher.dashboard') }}"
        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all duration-150
        {{ $currentRoute === 'teacher.dashboard' ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
        </svg>
        Dashboard
      </a>
    </div>

    {{-- PEMBELAJARAN --}}
    <div>
      <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-emerald-300/60">Pembelajaran</p>
      <div class="space-y-0.5">
        <a href="{{ route('teacher.materials.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'teacher.materials') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
          </svg>
          Materi
        </a>
        <a href="{{ route('teacher.assignments.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'teacher.assignments') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
          Tugas
        </a>
      </div>
    </div>

    {{-- UJIAN --}}
    <div>
      <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-emerald-300/60">Ujian</p>
      <div class="space-y-0.5">
        <a href="{{ route('teacher.exams.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'teacher.exams') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
          </svg>
          Ujian CBT
        </a>
        <a href="{{ route('teacher.questions.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'teacher.questions') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
          </svg>
          Bank Soal
        </a>
      </div>
    </div>
  </nav>

  {{-- User info --}}
  <div class="p-3 border-t border-white/10">
    <div class="flex items-center gap-3 rounded-lg px-3 py-2">
      <div class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white text-xs font-bold">
        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
        <p class="text-xs text-emerald-300 truncate">Guru</p>
      </div>
    </div>
  </div>
</div>