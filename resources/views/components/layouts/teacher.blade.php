<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f0f4f8]">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Teacher Panel' }} - Laravel E-Learning</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="h-full">
  @if(session('impersonating_admin_id'))
    <div
      class="fixed top-0 left-0 right-0 lg:left-64 z-[45] bg-amber-500 text-white text-center py-2 px-4 text-sm font-medium flex items-center justify-center gap-3">
      <span>⚠️ Anda sedang login sebagai <strong>{{ auth()->user()->name }}</strong> (mode impersonate)</span>
      <a href="/admin/stop-impersonate"
        class="inline-flex items-center gap-1 bg-white text-amber-700 px-3 py-1 rounded-md text-xs font-bold hover:bg-amber-50 transition">
        ← Kembali ke Admin
      </a>
    </div>
    <div class="h-10"></div>
  @endif
  <div x-data="{ sidebarOpen: false }" class="min-h-full">
    {{-- Mobile sidebar --}}
    <div x-show="sidebarOpen" x-cloak class="relative z-50 lg:hidden">
      <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/80" @click="sidebarOpen = false">
      </div>
      <div class="fixed inset-0 flex">
        <div x-show="sidebarOpen" x-transition:enter="transform transition ease-in-out duration-300"
          x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0"
          x-transition:leave-end="-translate-x-full" class="relative mr-16 flex w-full max-w-xs flex-1">
          @include('layouts.teacher-sidebar')
        </div>
      </div>
    </div>

    {{-- Desktop sidebar --}}
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
      @include('layouts.teacher-sidebar')
    </div>

    {{-- Main content --}}
    <div class="lg:pl-64">
      {{-- Top bar --}}
      <div class="sticky top-0 z-40 flex h-16 items-center gap-x-4 bg-white px-4 shadow-sm sm:px-6 lg:px-8">
        <button type="button" class="-m-2.5 p-2.5 text-gray-500 lg:hidden" @click="sidebarOpen = true">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>
        <div class="h-6 w-px bg-gray-200 lg:hidden"></div>
        <div class="flex flex-1 items-center">
          <h1 class="text-base font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
        </div>
        <div class="flex items-center gap-x-4">
          <button class="relative rounded-full p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
          </button>
          <div class="hidden sm:block h-6 w-px bg-gray-200"></div>
          <div class="flex items-center gap-x-3">
            <div
              class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-600 text-white text-xs font-bold">
              {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="hidden sm:block">
              <p class="text-sm font-semibold text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
              <p class="text-xs text-gray-500">Guru</p>
            </div>
          </div>

          {{-- Logout with confirmation --}}
          <div x-data="{ showLogout: false }" class="relative">
            <button @click="showLogout = true"
              class="rounded-lg p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 transition" title="Logout">
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

      <main class="py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
          <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-3">
            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-green-800">{{ session('success') }}</p>
          </div>
        @endif
        @if(session('error'))
          <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 flex items-center gap-3">
            <svg class="h-5 w-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <p class="text-sm text-red-800">{{ session('error') }}</p>
          </div>
        @endif
        {{ $slot }}
      </main>
    </div>
  </div>
  @livewireScripts
</body>

</html>