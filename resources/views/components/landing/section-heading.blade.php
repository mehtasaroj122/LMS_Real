@props([
    'eyebrow',
    'title',
    'copy' => null,
    'align' => 'center',
])

@php
    $alignmentClass = $align === 'left'
        ? 'max-w-2xl text-left'
        : 'mx-auto max-w-3xl text-center';
@endphp

<div {{ $attributes->class([$alignmentClass]) }}>
    <p class="landing-kicker">
        {{ $eyebrow }}
    </p>

    <h2 class="mt-5 text-3xl font-black tracking-tight text-slate-950 dark:text-slate-50 sm:text-4xl lg:text-5xl">
        {{ $title }}
    </h2>

    @if ($copy)
        <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300 sm:text-lg">
            {{ $copy }}
        </p>
    @endif
</div>
