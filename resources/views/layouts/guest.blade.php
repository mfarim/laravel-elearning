<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Laravel E-Learning</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="antialiased">
    <div class="min-h-screen flex">
        {{-- Left Panel — Blue Branding --}}
        <div class="hidden lg:flex lg:w-[45%] bg-gradient-to-br from-[#059669] to-[#064e3b] relative overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between p-10 w-full">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white">Laravel E-Learning</span>
                </a>

                {{-- Main Content --}}
                <div>
                    <div
                        class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 mb-6">
                        <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        <span class="text-sm text-white/90">Platform Laravel E-Learning #1 Indonesia</span>
                    </div>
                    <h1 class="text-4xl font-extrabold text-white leading-tight mb-4">Selamat Datang<br>Kembali!</h1>
                    <p class="text-lg text-emerald-200 mb-10 max-w-md">Kelola pembelajaran dan ujian CBT sekolah Anda
                        dengan mudah, aman, dan efisien.</p>

                    {{-- Stats --}}
                    <div class="flex gap-4 mb-10">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-center">
                            <p class="text-2xl font-bold text-white">100+</p>
                            <p class="text-xs text-emerald-200">Sekolah*</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-center">
                            <p class="text-2xl font-bold text-white">50K+</p>
                            <p class="text-xs text-emerald-200">Siswa*</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-center">
                            <p class="text-2xl font-bold text-white">99%</p>
                            <p class="text-xs text-emerald-200">Uptime*</p>
                        </div>
                    </div>
                    <p class="text-xs text-emerald-300/60 mb-6">*Data ilustrasi</p>

                    {{-- Feature bullets --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                            <span class="text-sm text-emerald-100">Dashboard real-time & analytics</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                            </div>
                            <span class="text-sm text-emerald-100">Ujian CBT dengan anti-cheat & timer</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                            </div>
                            <span class="text-sm text-emerald-100">Mobile-friendly untuk semua perangkat</span>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <p class="text-xs text-emerald-300/40">© {{ date('Y') }} Laravel E-Learning. All rights reserved.</p>
            </div>

            {{-- Decorative circles --}}
            <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-32 -left-16 w-80 h-80 bg-white/5 rounded-full"></div>
        </div>

        {{-- Right Panel — Form --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-10 bg-white">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>