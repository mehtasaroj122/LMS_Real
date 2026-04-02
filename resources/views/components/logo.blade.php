@props([
    'size' => 'md',
    'alt' => null,
    'lazy' => true,
    'sync' => true,
    'branding' => null,
])

@php
    $branding = $branding ?? $libraryBranding ?? [];
    $sizeMap = [
        'xs' => 32,
        'sm' => 40,
        'md' => 48,
        'lg' => 56,
        'xl' => 64,
        '2xl' => 80,
        'hero' => 96,
    ];

    $resolvedSize = is_numeric($size) ? (int) $size : ($sizeMap[$size] ?? $sizeMap['md']);
    $resolvedImageUrl = trim((string) ($branding['image_url'] ?? ''));
    $resolvedFallbackText = trim((string) ($branding['fallback_text'] ?? 'LMS')) ?: 'LMS';
    $resolvedAlt = trim((string) ($alt ?: ($branding['alt'] ?? 'Library Logo'))) ?: 'Library Logo';
@endphp

@once
    <style>
        .library-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            line-height: 1;
            vertical-align: middle;
        }

        .library-logo__badge {
            position: relative;
            width: var(--library-logo-size, 48px);
            height: var(--library-logo-size, 48px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 999px;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.28), transparent 44%),
                linear-gradient(135deg, #1d4ed8 0%, #0f766e 100%);
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 14px 28px -20px rgba(15, 23, 42, 0.75);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .library-logo__badge.has-image {
            background: transparent;
            border: none;
            box-shadow: none;
        }

        .library-logo:hover .library-logo__badge {
            transform: scale(1.03);
            box-shadow: 0 18px 32px -22px rgba(15, 23, 42, 0.82);
        }

        .library-logo__image,
        .library-logo__fallback {
            width: 100%;
            height: 100%;
        }

        .library-logo__image {
            display: block;
            object-fit: cover;
            object-position: center;
            background: rgba(255, 255, 255, 0.95);
        }

        .library-logo__fallback {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.28em;
            font-weight: 800;
            font-size: calc(var(--library-logo-size, 48px) * 0.34);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(15, 23, 42, 0.18);
            white-space: nowrap;
        }
    </style>
@endonce

<span
    {{ $attributes->merge(['class' => 'library-logo']) }}
    style="--library-logo-size: {{ $resolvedSize }}px;"
    @if($sync)
        data-library-logo-root
        data-library-logo-name="{{ $branding['name'] ?? '' }}"
        data-library-logo-image-url="{{ $resolvedImageUrl }}"
        data-library-logo-fallback-text="{{ $resolvedFallbackText }}"
        data-library-logo-alt="{{ $resolvedAlt }}"
    @endif
>
    <span class="library-logo__badge" data-library-logo-badge>
        <img
            class="library-logo__image"
            data-library-logo-image
            src="{{ $resolvedImageUrl }}"
            alt="{{ $resolvedAlt }}"
            loading="{{ $lazy ? 'lazy' : 'eager' }}"
            decoding="async"
            style="{{ $resolvedImageUrl !== '' ? '' : 'display: none;' }}"
        >
        <span class="library-logo__fallback" data-library-logo-fallback style="{{ $resolvedImageUrl === '' ? '' : 'display: none;' }}">
            {{ $resolvedFallbackText }}
        </span>
    </span>
</span>
