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
      <a href="{{ route('admin.dashboard') }}"
        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all duration-150
        {{ $currentRoute === 'admin.dashboard' ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
        </svg>
        Dashboard
      </a>
    </div>

    {{-- MANAJEMEN --}}
    <div>
      <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-emerald-300/60">Manajemen</p>
      <div class="space-y-0.5">
        <a href="{{ route('admin.teachers.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'admin.teachers') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
          </svg>
          Guru
        </a>
        <a href="{{ route('admin.students.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'admin.students') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
          </svg>
          Siswa
        </a>
        <a href="{{ route('admin.classrooms.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'admin.classrooms') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
          </svg>
          Kelas
        </a>
      </div>
    </div>

    {{-- AKADEMIK --}}
    <div>
      <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-emerald-300/60">Akademik</p>
      <div class="space-y-0.5">
        <a href="{{ route('admin.subjects.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'admin.subjects') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
          </svg>
          Mata Pelajaran
        </a>
      </div>
    </div>

    {{-- SISTEM --}}
    <div>
      <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-emerald-300/60">Sistem</p>
      <div class="space-y-0.5">
        <a href="{{ route('admin.announcements.index') }}"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150
          {{ str_starts_with($currentRoute, 'admin.announcements') ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/10' }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46" />
          </svg>
          Pengumuman
        </a>
      </div>
    </div>
  </nav>

  {{-- User info at bottom --}}
  <div class="p-3 border-t border-white/10">
    <div class="flex items-center gap-3 rounded-lg px-3 py-2">
      <div class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white text-xs font-bold">
        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
        <p class="text-xs text-emerald-300 truncate">Administrator</p>
      </div>
    </div>
  </div>
</div>