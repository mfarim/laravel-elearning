<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Student' }} - Laravel E-Learning</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>

<body class="h-full">
  @if(session('impersonating_admin_id'))
    <div
      class="fixed top-0 left-0 right-0 z-[60] bg-amber-500 text-white text-center py-2 px-4 text-sm font-medium flex items-center justify-center gap-3">
      <span>⚠️ Anda sedang login sebagai <strong>{{ auth()->user()->name }}</strong> (mode impersonate)</span>
      <a href="/admin/stop-impersonate"
        class="inline-flex items-center gap-1 bg-white text-amber-700 px-3 py-1 rounded-md text-xs font-bold hover:bg-amber-50 transition">
        ← Kembali ke Admin
      </a>
    </div>
    <div class="h-10"></div>
  @endif
  <div class="min-h-full pb-20">
    {{-- Top Header --}}
    <div class="sticky top-0 z-40 bg-emerald-600 text-white px-4 py-3 shadow-md">
      <div class="flex items-center justify-between max-w-lg mx-auto">
        <div>
          <p class="text-sm opacity-80">Halo,</p>
          <h1 class="text-lg font-bold leading-tight">{{ Auth::user()->name }}</h1>
        </div>
        <div x-data="{ showLogout: false }" class="relative">
          <button @click="showLogout = true" class="p-2 rounded-full hover:bg-emerald-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
            </svg>
          </button>
          <template x-teleport="body">
            <div x-show="showLogout" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
              x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
              x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
              x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
              <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showLogout = false"></div>
              <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-center w-14 h-14 rounded-full bg-red-50 mx-auto mb-4">
                  <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                  </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Keluar dari Aplikasi?</h3>
                <p class="text-sm text-gray-500 mb-6">Yakin ingin keluar? Anda perlu login kembali untuk mengakses
                  sistem.</p>
                <div class="flex gap-3">
                  <button @click="showLogout = false"
                    class="flex-1 rounded-xl bg-gray-100 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition">Batal</button>
                  <form method="POST" action="{{ route('logout') }}" class="flex-1">@csrf
                    <button type="submit"
                      class="w-full rounded-xl bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-500 transition">Ya,
                      Keluar</button>
                  </form>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    {{-- Main Content --}}
    <main class="max-w-lg mx-auto px-4 py-4">
      @if(session('success'))
      <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
      @if(session('error'))
      <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>@endif
      {{ $slot }}
    </main>

    {{-- Bottom Navigation --}}
    @php $r = request()->route()?->getName() ?? ''; @endphp
    <nav class="fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 shadow-lg">
      <div class="max-w-lg mx-auto flex justify-around py-2">
        <a href="{{ route('student.dashboard') }}"
          class="flex flex-col items-center px-3 py-1 {{ $r === 'student.dashboard' ? 'text-emerald-600' : 'text-gray-400' }}">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
          </svg>
          <span class="text-xs mt-0.5">Home</span>
        </a>
        <a href="{{ route('student.materials') }}"
          class="flex flex-col items-center px-3 py-1 {{ str_starts_with($r, 'student.material') ? 'text-emerald-600' : 'text-gray-400' }}">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
          </svg>
          <span class="text-xs mt-0.5">Materi</span>
        </a>
        <a href="{{ route('student.exams') }}"
          class="flex flex-col items-center px-3 py-1 {{ str_starts_with($r, 'student.exam') ? 'text-emerald-600' : 'text-gray-400' }}">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
          </svg>
          <span class="text-xs mt-0.5">Ujian</span>
        </a>
        <a href="{{ route('student.assignments') }}"
          class="flex flex-col items-center px-3 py-1 {{ str_starts_with($r, 'student.assignment') ? 'text-emerald-600' : 'text-gray-400' }}">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
          <span class="text-xs mt-0.5">Tugas</span>
        </a>
        <a href="{{ route('student.grades') }}"
          class="flex flex-col items-center px-3 py-1 {{ str_starts_with($r, 'student.grade') ? 'text-emerald-600' : 'text-gray-400' }}">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
          </svg>
          <span class="text-xs mt-0.5">Nilai</span>
        </a>
      </div>
    </nav>
  </div>
  @livewireScripts
</body>

</html>