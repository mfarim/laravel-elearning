@props(['withText' => false])

@if($withText)
    <div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
        <img src="{{ asset('images/logo.png') }}" alt="Laravel E-Learning" class="h-9 w-9 rounded-xl object-cover shadow-md shadow-emerald-500/20">
        <span class="font-bold text-lg text-gray-900 tracking-tight">Laravel <span class="text-emerald-600">E-Learning</span></span>
    </div>
@else
    <img src="{{ asset('images/logo.png') }}" alt="Laravel E-Learning" {{ $attributes->merge(['class' => 'h-9 w-9 rounded-xl object-cover shadow-md shadow-emerald-500/20']) }}>
@endif
