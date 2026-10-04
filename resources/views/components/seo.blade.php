@props([
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
])

@include('partials.seo', [
    'title' => $title,
    'description' => $description,
    'keywords' => $keywords,
    'image' => $image,
])
