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
        return response()->json(array_merge([
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'icons' => $this->icons($settings),
        ], $payload))->header('Content-Type', 'application/manifest+json');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function icons(Setting $settings): array
    {
        $icons = [];

        $rawLogo = method_exists($settings, 'getRawOriginal')
            ? $settings->getRawOriginal('logo')
            : null;
        $logoUrl = PublicStorageUrl::fromPath($rawLogo);

        if ($logoUrl) {
            $absolute = $this->absoluteUrl($logoUrl);
            $icons[] = [
                'src' => $absolute,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ];
            $icons[] = [
                'src' => $absolute,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ];
        }

        foreach ([192, 512] as $size) {
            $icons[] = [
                'src' => url("/pwa/icon-{$size}.png"),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ];
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
}
