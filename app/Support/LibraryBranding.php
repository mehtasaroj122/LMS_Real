<?php

namespace App\Support;

use App\Models\FineSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LibraryBranding
{
    private const CACHE_KEY = 'library-branding.active';

    public static function resolve(bool $fresh = false): array
    {
        if (!self::canUseDatabase()) {
            return self::defaults();
        }

        if ($fresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::rememberForever(self::CACHE_KEY, fn () => self::build());
    }

    public static function refresh(): array
    {
        return self::resolve(true);
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function canUseDatabase(): bool
    {
        try {
            return Schema::hasTable('fine_settings');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function build(): array
    {
        $fineSetting = FineSetting::resolveActive();
        $fallbackText = trim((string) ($fineSetting->logo_fallback_text ?? FineSetting::DEFAULTS['logo_fallback_text']));
        $fallbackText = $fallbackText !== '' ? $fallbackText : FineSetting::DEFAULTS['logo_fallback_text'];
        $updatedAt = optional($fineSetting->updated_at);
        $logoUrl = self::resolveLogoUrl($fineSetting->logo_path, $updatedAt?->timestamp);

        return [
            'name' => self::resolveSystemTitle(),
            'image_url' => $logoUrl,
            'fallback_text' => $fallbackText,
            'alt' => 'Library Logo',
            'has_custom_logo' => $logoUrl !== null,
            'version' => $updatedAt?->timestamp,
            'updated_at' => $updatedAt?->toIso8601String(),
        ];
    }

    private static function defaults(): array
    {
        return [
            'name' => self::resolveSystemTitle(),
            'image_url' => null,
            'fallback_text' => FineSetting::DEFAULTS['logo_fallback_text'],
            'alt' => 'Library Logo',
            'has_custom_logo' => false,
            'version' => null,
            'updated_at' => null,
        ];
    }

    private static function resolveLogoUrl(?string $logoPath, ?int $version = null): ?string
    {
        $normalizedPath = self::normalizeStoragePath($logoPath);
        if (!$normalizedPath) {
            return null;
        }

        $publicPath = public_path('storage/' . $normalizedPath);

        if (!is_file($publicPath)) {
            return null;
        }

        $url = '/storage/' . ltrim($normalizedPath, '/');

        if ($version) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url = "{$url}{$separator}v={$version}";
        }

        return $url;
    }

    private static function normalizeStoragePath(?string $path): ?string
    {
        $normalizedPath = trim((string) $path);
        if ($normalizedPath === '') {
            return null;
        }

        if (str_starts_with($normalizedPath, 'storage/')) {
            return substr($normalizedPath, 8);
        }

        return ltrim($normalizedPath, '/');
    }

    private static function resolveSystemTitle(): string
    {
        $appName = trim((string) config('app.name', ''));

        if ($appName === '' || strcasecmp($appName, 'Laravel') === 0) {
            return 'Library Management System';
        }

        return $appName;
    }
}
