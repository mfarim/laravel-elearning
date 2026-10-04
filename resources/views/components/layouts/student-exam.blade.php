<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  @include('partials.seo', [
      'title' => $title ?? 'Ujian CBT',
  ])
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>

<body class="h-full bg-gray-100">
  {{ $slot }}
  @livewireScripts
</body>

</html>