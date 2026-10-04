@php
    $appDefaultTitle = config('app.name', 'Laravel') . ' E-Learning & CBT — Platform Pembelajaran & Ujian Digital';
    $metaTitle = !empty($title) ? $title . ' - ' . config('app.name', 'Laravel') . ' E-Learning' : $appDefaultTitle;
    $metaDescription = $description ?? 'Platform E-Learning dan Computer Based Test (CBT) modern untuk sekolah. Kelola materi, tugas, ujian daring anti-cheat, dan penilaian real-time secara digital.';
    $metaKeywords = $keywords ?? 'laravel elearning, cbt sekolah, ujian online, ujian cbt, aplikasi pembelajaran online, livewire cbt, elearning cbt indonesia';
    $metaImage = !empty($image) ? $image : asset('images/og-image.png');
    $currentUrl = url()->current();

    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                '@id' => url('/') . '#webapp',
                'name' => 'Laravel E-Learning & CBT Platform',
                'url' => url('/'),
                'description' => $metaDescription,
                'applicationCategory' => 'EducationalApplication',
                'operatingSystem' => 'All',
                'browserRequirements' => 'Requires JavaScript. Requires HTML5.',
                'image' => asset('images/og-image.png'),
                'screenshot' => asset('images/og-image.png'),
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'IDR',
                ],
            ],
            [
                '@type' => 'Organization',
                '@id' => url('/') . '#organization',
                'name' => 'Laravel E-Learning',
                'url' => url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                    'width' => 512,
                    'height' => 512,
                ],
            ],
        ],
    ];
@endphp

<!-- Basic SEO -->
<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ $metaKeywords }}">
<meta name="author" content="Laravel E-Learning & CBT">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<link rel="canonical" href="{{ $currentUrl }}">

<!-- Favicons & Mobile Icons -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/favicon.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#059669">
<meta name="msapplication-TileColor" content="#059669">
<meta name="msapplication-TileImage" content="{{ asset('images/apple-touch-icon.png') }}">

<!-- Open Graph / Facebook / WhatsApp / LinkedIn -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $currentUrl }}">
<meta property="og:site_name" content="Laravel E-Learning & CBT Platform">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:secure_url" content="{{ $metaImage }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Laravel E-Learning & CBT Platform">
<meta property="og:locale" content="id_ID">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $currentUrl }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
<meta name="twitter:image:alt" content="Laravel E-Learning & CBT Platform">

<!-- JSON-LD Structured Data for Google Search Engine Snippets -->
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
</script>
