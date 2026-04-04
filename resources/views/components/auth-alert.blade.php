@props([
    'variant' => 'info',
    'message' => null,
    'messages' => [],
])

@php
    $resolvedMessages = [];

    if ($message !== null && $message !== '') {
        $resolvedMessages = is_array($message) ? $message : [$message];
    } elseif (! empty($messages)) {
        $resolvedMessages = is_array($messages) ? $messages : [$messages];
    }

    $resolvedMessages = array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $resolvedMessages)));

    $variants = [
        'success' => [
            'wrapper' => 'border-emerald-200 bg-emerald-50/90 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100',
            'role' => 'status',
            'live' => 'polite',
            'icon' => 'success',
        ],
        'danger' => [
            'wrapper' => 'border-rose-200 bg-rose-50/90 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-100',
            'role' => 'alert',
            'live' => 'assertive',
            'icon' => 'danger',
        ],
        'warning' => [
            'wrapper' => 'border-amber-200 bg-amber-50/90 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100',
            'role' => 'alert',
            'live' => 'assertive',
            'icon' => 'warning',
        ],
        'info' => [
            'wrapper' => 'border-sky-200 bg-sky-50/90 text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100',
            'role' => 'status',
            'live' => 'polite',
            'icon' => 'info',
        ],
    ];

    $resolvedVariant = $variants[$variant] ?? $variants['info'];
    $wrapperClasses = trim(($attributes->get('class') ?? '') . ' rounded-2xl border px-4 py-3 text-sm ' . $resolvedVariant['wrapper']);
@endphp

@if ($resolvedMessages !== [])
    <div
        {{ $attributes->except('class')->merge(['class' => $wrapperClasses]) }}
        role="{{ $resolvedVariant['role'] }}"
        aria-live="{{ $resolvedVariant['live'] }}"
    >
        <div class="flex items-start gap-3">
            <span class="mt-0.5 shrink-0">
                @if ($resolvedVariant['icon'] === 'success')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                @elseif ($resolvedVariant['icon'] === 'danger')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4" />
                        <path d="M12 16h.01" />
                    </svg>
                @elseif ($resolvedVariant['icon'] === 'warning')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                    </svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                @endif
            </span>

            <div class="space-y-1">
                @foreach ($resolvedMessages as $resolvedMessage)
                    <p>{{ $resolvedMessage }}</p>
                @endforeach
            </div>
        </div>
    </div>
@endif
