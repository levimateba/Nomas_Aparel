<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\PublicStorageUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ManifestController extends Controller
{
    public function storefront(): JsonResponse
    {
        $settings = Setting::get_settings();
        $name = trim((string) ($settings->site_name ?: config('app.name', 'Store')));

        return $this->manifestResponse([
            'name' => $name,
            'short_name' => Str::limit($name, 12, ''),
            'description' => trim((string) ($settings->site_tagline ?: 'Shop with us on the go.')),
            'start_url' => url('/'),
            'scope' => url('/'),
            'theme_color' => config('pwa.theme_color', '#16456e'),
            'background_color' => config('pwa.background_color', '#ffffff'),
            'categories' => ['shopping', 'business'],
        ], $settings);
    }

    public function admin(): JsonResponse
    {
        $settings = Setting::get_settings();
        $name = trim((string) ($settings->site_name ?: config('app.name', 'Store')));
        $startPath = config('pwa.admin_start_url', '/admin/pos');

        return $this->manifestResponse([
            'name' => $name.' Staff',
            'short_name' => Str::limit($name.' POS', 12, ''),
            'description' => 'Point of sale and store management for staff.',
            'start_url' => url($startPath),
            'scope' => url('/admin/'),
            'theme_color' => config('pwa.admin_theme_color', '#121212'),
            'background_color' => config('pwa.admin_background_color', '#121212'),
            'categories' => ['business', 'productivity'],
        ], $settings);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function manifestResponse(array $payload, Setting $settings): JsonResponse
    {
        $version = (string) config('pwa.cache_version', '1');

        return response()->json(array_merge([
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'icons' => $this->icons($settings, $version),
        ], $payload))->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-cache, must-revalidate');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function icons(Setting $settings, string $version): array
    {
        $icons = [];
        $bust = '?v='.$version;

        // Prefer generated PWA PNGs (correct size + type for Android/iOS launchers).
        foreach ([192, 512] as $size) {
            $anyPath = public_path("pwa/icon-{$size}.png");
            $maskPath = public_path("pwa/icon-{$size}-maskable.png");

            if (is_file($anyPath)) {
                $icons[] = [
                    'src' => url("/pwa/icon-{$size}.png").$bust,
                    'sizes' => "{$size}x{$size}",
                    'type' => 'image/png',
                    'purpose' => 'any',
                ];
            }

            if (is_file($maskPath)) {
                $icons[] = [
                    'src' => url("/pwa/icon-{$size}-maskable.png").$bust,
                    'sizes' => "{$size}x{$size}",
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ];
            } elseif (is_file($anyPath)) {
                $icons[] = [
                    'src' => url("/pwa/icon-{$size}.png").$bust,
                    'sizes' => "{$size}x{$size}",
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ];
            }
        }

        // Fallback to uploaded site logo if generated icons are missing.
        if ($icons === []) {
            $rawLogo = method_exists($settings, 'getRawOriginal')
                ? $settings->getRawOriginal('logo')
                : null;
            $logoUrl = PublicStorageUrl::fromPath($rawLogo);
            if ($logoUrl) {
                $absolute = $this->absoluteUrl($logoUrl);
                $type = $this->guessImageType($absolute);
                foreach (['512x512', '192x192'] as $sizes) {
                    $icons[] = [
                        'src' => $absolute,
                        'sizes' => $sizes,
                        'type' => $type,
                        'purpose' => 'any',
                    ];
                }
            }
        }

        return $icons;
    }

    private function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    private function guessImageType(string $url): string
    {
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?: $url);
        if (str_ends_with($path, '.jpg') || str_ends_with($path, '.jpeg')) {
            return 'image/jpeg';
        }
        if (str_ends_with($path, '.webp')) {
            return 'image/webp';
        }
        if (str_ends_with($path, '.svg')) {
            return 'image/svg+xml';
        }

        return 'image/png';
    }
}
