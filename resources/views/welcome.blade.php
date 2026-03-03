<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel E-Learning — Platform Pembelajaran & Ujian Digital</title>
    <meta name="description"
        content="Platform Laravel E-Learning & Computer Based Test (CBT) modern untuk sekolah. Kelola pembelajaran, ujian, dan penilaian secara digital.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-gradient {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 30%, #a7f3d0 60%, #6ee7b7 100%);
        }

        .feature-card:hover {
            transform: translateY(-4px);
        }

        .blob {
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float-delay {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        .animate-float-delay {
            animation: float-delay 8s ease-in-out infinite;
        }
    </style>
</head>

<body class="antialiased bg-white text-gray-800">

    {{-- ====== NAVBAR ====== --}}
    <nav x-data="{ open: false }"
        class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-600 shadow-lg shadow-emerald-200">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-gray-900">Laravel <span
                            class="text-emerald-600">E-Learning</span></span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#fitur"
                        class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition">Fitur</a>
                    <a href="#role"
                        class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition">Panel</a>
                    <a href="#cbt" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition">Ujian
                        CBT</a>
                    <a href="#tech"
                        class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition">Teknologi</a>
                </div>
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('login') }}"
                        class="text-sm font-semibold text-gray-700 hover:text-emerald-600 transition px-4 py-2">Masuk</a>
                    <a href="{{ route('login') }}"
                        class="text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl px-5 py-2.5 shadow-lg shadow-emerald-200 hover:shadow-emerald-300 transition-all">Mulai
                        Sekarang</a>
                </div>
                <button @click="open = !open" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
            <div x-show="open" x-cloak x-transition class="md:hidden pb-4 space-y-2">
                <a href="#fitur"
                    class="block py-2 px-3 rounded-lg text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-600">Fitur</a>
                <a href="#role"
                    class="block py-2 px-3 rounded-lg text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-600">Panel</a>
                <a href="#cbt"
                    class="block py-2 px-3 rounded-lg text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-600">Ujian
                    CBT</a>
                <a href="{{ route('login') }}"
                    class="block py-2.5 px-3 rounded-xl text-sm font-semibold text-white bg-emerald-600 text-center">Masuk</a>
            </div>
        </div>
    </nav>

    {{-- ====== HERO ====== --}}
    <section class="relative hero-gradient pt-32 pb-20 lg:pt-40 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 bg-emerald-100 rounded-full px-4 py-1.5 mb-6">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-sm font-semibold text-emerald-700">Platform Laravel E-Learning Modern</span>
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 leading-[1.1] mb-6">
                        Transformasi<br>Pembelajaran<br><span class="gradient-text">Digital Sekolah</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 max-w-lg leading-relaxed">
                        Platform Laravel E-Learning & Computer Based Test (CBT) lengkap untuk mengelola pembelajaran,
                        ujian,
                        tugas, dan penilaian secara digital. Dibangun dengan teknologi modern.
                    </p>
                    <div class="flex flex-wrap gap-4 mb-10">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold px-7 py-3.5 rounded-2xl shadow-lg shadow-emerald-200 hover:bg-emerald-500 hover:shadow-emerald-300 transition-all text-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Masuk ke Platform
                        </a>
                        <a href="#fitur"
                            class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold px-7 py-3.5 rounded-2xl shadow-sm border border-gray-200 hover:border-emerald-300 hover:text-emerald-600 transition-all text-sm">
                            Jelajahi Fitur
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                            </svg>
                        </a>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">3</p>
                            <p class="text-xs text-gray-500">Role Pengguna</p>
                        </div>
                        <div class="w-px h-10 bg-gray-200"></div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">14+</p>
                            <p class="text-xs text-gray-500">Model Data</p>
                        </div>
                        <div class="w-px h-10 bg-gray-200"></div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">19</p>
                            <p class="text-xs text-gray-500">Routes</p>
                        </div>
                    </div>
                </div>
                <div class="relative hidden lg:block">
                    {{-- Abstract illustration --}}
                    <div class="relative w-full h-[480px]">
                        <div class="absolute top-8 right-8 w-64 h-64 bg-emerald-200/60 blob animate-float"></div>
                        <div class="absolute bottom-12 left-4 w-48 h-48 bg-emerald-300/40 blob animate-float-delay">
                        </div>
                        {{-- Dashboard preview card --}}
                        <div
                            class="absolute top-16 left-8 right-16 bg-white rounded-2xl shadow-2xl shadow-emerald-100 p-6 border border-gray-100">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                <div class="ml-4 flex-1 h-4 bg-gray-100 rounded-full"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <div class="bg-emerald-50 rounded-xl p-3 text-center">
                                    <p class="text-xl font-bold text-emerald-700">24</p>
                                    <p class="text-[10px] text-emerald-600 font-medium">Guru</p>
                                </div>
                                <div class="bg-blue-50 rounded-xl p-3 text-center">
                                    <p class="text-xl font-bold text-blue-700">356</p>
                                    <p class="text-[10px] text-blue-600 font-medium">Siswa</p>
                                </div>
                                <div class="bg-amber-50 rounded-xl p-3 text-center">
                                    <p class="text-xl font-bold text-amber-700">12</p>
                                    <p class="text-[10px] text-amber-600 font-medium">Kelas</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-3 bg-gray-100 rounded-full w-full"></div>
                                <div class="h-3 bg-gray-100 rounded-full w-3/4"></div>
                                <div class="h-3 bg-gray-100 rounded-full w-5/6"></div>
                            </div>
                        </div>
                        {{-- Floating badges --}}
                        <div
                            class="absolute bottom-28 right-4 bg-white rounded-xl shadow-lg px-4 py-3 border border-gray-100 animate-float">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Auto-Grade</p>
                                    <p class="text-[10px] text-gray-500">Nilai otomatis PG</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====== FEATURES ====== --}}
    <section id="fitur" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 bg-emerald-50 rounded-full px-4 py-1.5 mb-4">
                    <span class="text-sm font-semibold text-emerald-600">✨ Fitur Unggulan</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Semua yang Dibutuhkan untuk<br><span
                        class="gradient-text">Pembelajaran Digital</span></h2>
                <p class="text-gray-500">Platform lengkap untuk mengelola seluruh aspek pembelajaran, dari materi hingga
                    penilaian.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Feature 1 --}}
                <div
                    class="feature-card bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Ujian CBT</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Ujian digital dengan timer countdown, anti-cheat
                        (fullscreen + tab detection), auto-save jawaban, dan scoring otomatis.</p>
                </div>

                {{-- Feature 2 --}}
                <div
                    class="feature-card bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Materi Pembelajaran</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Upload materi dalam 5 format: dokumen, video, teks,
                        link, dan audio. Siswa akses langsung dari device mereka.</p>
                </div>

                {{-- Feature 3 --}}
                <div
                    class="feature-card bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Manajemen Tugas</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Buat dan kelola tugas dengan deadline, terima
                        submission file dari siswa, dan pantau status pengumpulan.</p>
                </div>

                {{-- Feature 4 --}}
                <div
                    class="feature-card bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Bank Soal</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Kelola soal PG (A-E), isian singkat, dan essay.
                        Dilengkapi level kesulitan dan pengacakan soal & opsi otomatis.</p>
                </div>

                {{-- Feature 5 --}}
                <div
                    class="feature-card bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Monitor Realtime</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Pantau progress ujian siswa secara realtime dengan
                        auto-refresh setiap 5 detik. Lihat jawaban dan sisa waktu.</p>
                </div>

                {{-- Feature 6 --}}
                <div
                    class="feature-card bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Penilaian & Rapor</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Rata-rata per mata pelajaran, progress bar visual,
                        status lulus/gagal berdasarkan KKM yang dapat dikustomisasi.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ====== ROLE PANELS ====== --}}
    <section id="role" class="py-20 lg:py-28 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 bg-emerald-50 rounded-full px-4 py-1.5 mb-4">
                    <span class="text-sm font-semibold text-emerald-600">👥 Multi-Role</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Tiga Panel untuk<br><span
                        class="gradient-text">Setiap Peran</span></h2>
                <p class="text-gray-500">Setiap pengguna memiliki dashboard khusus sesuai kebutuhan perannya.</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Admin --}}
                <div
                    class="bg-white rounded-3xl border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Admin Panel</h3>
                                <p class="text-emerald-100 text-xs">/admin</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                        stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Dashboard statistik: guru, siswa,
                                    kelas, mapel</span></li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                        stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">CRUD Guru + auto buat akun
                                    login</span></li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                        stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">CRUD Siswa + filter kelas + Import
                                    Excel</span></li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                        stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Manajemen kelas, mapel,
                                    pengumuman</span></li>
                        </ul>
                    </div>
                </div>

                {{-- Teacher --}}
                <div
                    class="bg-white rounded-3xl border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Teacher Panel</h3>
                                <p class="text-blue-100 text-xs">/teacher</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Upload materi: dokumen, video,
                                    teks, link, audio</span></li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Buat ujian CBT + bank soal
                                    (PG/isian/essay)</span></li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Monitor ujian realtime +
                                    auto-refresh 5 detik</span></li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Print: kartu ujian, daftar hadir,
                                    berita acara</span></li>
                        </ul>
                    </div>
                </div>

                {{-- Student --}}
                <div
                    class="bg-white rounded-3xl border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Student Panel</h3>
                                <p class="text-amber-100 text-xs">/student — Mobile-First</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Dashboard: ujian, tugas aktif,
                                    pengumuman</span></li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Akses materi + download file</span>
                            </li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Ikuti ujian CBT + submit tugas
                                    online</span></li>
                            <li class="flex items-start gap-3"><span
                                    class="flex-shrink-0 w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center mt-0.5"><svg
                                        class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg></span><span class="text-sm text-gray-600">Lihat nilai + progress bar per
                                    mapel</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====== CBT FLOW ====== --}}
    <section id="cbt" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 bg-emerald-50 rounded-full px-4 py-1.5 mb-4">
                        <span class="text-sm font-semibold text-emerald-600">🖥️ Computer Based Test</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6">Ujian Digital yang<br><span
                            class="gradient-text">Aman & Andal</span></h2>
                    <p class="text-gray-500 mb-8 leading-relaxed">Sistem ujian CBT yang dirancang untuk memastikan
                        integritas ujian dengan fitur keamanan canggih dan pengalaman pengguna yang lancar.</p>

                    <div class="space-y-5">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                                <span class="text-lg">⏱️</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm">Timer Countdown</h4>
                                <p class="text-sm text-gray-500 mt-0.5">Auto-submit saat waktu habis, siswa tidak perlu
                                    khawatir lupa submit.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                                <span class="text-lg">🔒</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm">Anti-Cheat System</h4>
                                <p class="text-sm text-gray-500 mt-0.5">Mode fullscreen + deteksi pergantian tab untuk
                                    mencegah kecurangan.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <span class="text-lg">📍</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm">Navigasi Soal</h4>
                                <p class="text-sm text-gray-500 mt-0.5">Badge status terjawab/belum, lompat ke soal
                                    manapun dengan mudah.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                <span class="text-lg">💾</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm">Auto-Save & Auto-Grade</h4>
                                <p class="text-sm text-gray-500 mt-0.5">Jawaban tersimpan otomatis setiap klik. Nilai PG
                                    langsung dihitung.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Flow diagram --}}
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-3xl p-8 lg:p-10">
                    <h3 class="text-sm font-bold text-emerald-700 uppercase tracking-wider mb-6">Alur Ujian CBT</h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                                1</div>
                            <div class="flex-1 bg-white rounded-xl px-4 py-3 shadow-sm">
                                <p class="text-sm font-medium text-gray-800">Siswa membuka halaman Ujian</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 ml-5">
                            <div class="w-0.5 h-6 bg-emerald-300"></div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                                2</div>
                            <div class="flex-1 bg-white rounded-xl px-4 py-3 shadow-sm">
                                <p class="text-sm font-medium text-gray-800">Konfirmasi & masuk mode ujian</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 ml-5">
                            <div class="w-0.5 h-6 bg-emerald-300"></div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                                3</div>
                            <div class="flex-1 bg-white rounded-xl px-4 py-3 shadow-sm">
                                <p class="text-sm font-medium text-gray-800">Mengerjakan soal</p>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <span
                                        class="text-[10px] font-semibold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Timer</span>
                                    <span
                                        class="text-[10px] font-semibold bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Anti-Cheat</span>
                                    <span
                                        class="text-[10px] font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Auto-Save</span>
                                    <span
                                        class="text-[10px] font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Navigasi</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 ml-5">
                            <div class="w-0.5 h-6 bg-emerald-300"></div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                                4</div>
                            <div class="flex-1 bg-white rounded-xl px-4 py-3 shadow-sm">
                                <p class="text-sm font-medium text-gray-800">Submit & auto-scoring PG ✅</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====== TECH STACK ====== --}}
    <section id="tech" class="py-20 lg:py-28 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 bg-emerald-50 rounded-full px-4 py-1.5 mb-4">
                    <span class="text-sm font-semibold text-emerald-600">🛠️ Tech Stack</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Dibangun dengan<br><span
                        class="gradient-text">Teknologi Modern</span></h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div
                    class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg hover:border-emerald-200 transition-all">
                    <div class="text-3xl mb-2">🐘</div>
                    <p class="text-sm font-bold text-gray-900">Laravel 12</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Backend</p>
                </div>
                <div
                    class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg hover:border-emerald-200 transition-all">
                    <div class="text-3xl mb-2">⚡</div>
                    <p class="text-sm font-bold text-gray-900">Livewire 3</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Reactive UI</p>
                </div>
                <div
                    class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg hover:border-emerald-200 transition-all">
                    <div class="text-3xl mb-2">🏔️</div>
                    <p class="text-sm font-bold text-gray-900">Alpine.js</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Interaktif</p>
                </div>
                <div
                    class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg hover:border-emerald-200 transition-all">
                    <div class="text-3xl mb-2">🎨</div>
                    <p class="text-sm font-bold text-gray-900">Tailwind CSS</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Styling</p>
                </div>
                <div
                    class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg hover:border-emerald-200 transition-all">
                    <div class="text-3xl mb-2">🔐</div>
                    <p class="text-sm font-bold text-gray-900">Spatie</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Permission</p>
                </div>
                <div
                    class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg hover:border-emerald-200 transition-all">
                    <div class="text-3xl mb-2">📊</div>
                    <p class="text-sm font-bold text-gray-900">Excel Import</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Maatwebsite</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ====== CTA ====== --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-500 rounded-3xl p-10 lg:p-16 text-center relative overflow-hidden">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-white/10 rounded-full"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Siap Memulai?</h2>
                    <p class="text-emerald-100 mb-8 max-w-lg mx-auto">Login sekarang dan jelajahi semua fitur Laravel
                        E-Learning
                        CBT untuk kebutuhan pembelajaran digital sekolah Anda.</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 bg-white text-emerald-700 font-bold px-8 py-4 rounded-2xl shadow-lg hover:shadow-xl transition-all text-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Masuk ke Platform
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====== FOOTER ====== --}}
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-600">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-white">Laravel E-Learning</span>
                </div>
                <p class="text-sm">Laravel 12 · Livewire 3 · Tailwind CSS · Alpine.js</p>
                <p class="text-sm">&copy; {{ date('Y') }} Laravel E-Learning. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>

</html>