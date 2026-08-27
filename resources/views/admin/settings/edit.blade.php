@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    @php
        $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
            ? $settings->getRawOriginal('logo')
            : ($settings->logo ?? null);
        $logoPreview = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
        $footerPreview = trim((string) ($settings->footer_text ?: ('© ' . now()->year . ' ' . ($settings->site_name ?? 'Store') . '. All rights reserved.')));
    @endphp
    <style>
        .form-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 16px; }
        .brand-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .brand-fields .full { grid-column: 1 / -1; }
        .footer-preview {
            background: #121212; color: #f3e9c4; border-radius: 14px; padding: 18px;
            border: 1px solid #d4af37; min-height: 140px;
        }
        .footer-preview h4 { margin: 0 0 8px; color: #d4af37; font-size: 13px; letter-spacing: .08em; text-transform: uppercase; }
        .preview-logo { width: 64px; height: 64px; object-fit: cover; border-radius: 12px; border: 1px solid #d9e0e5; margin-top: 8px; }
        @media (max-width: 900px) {
            .form-grid, .brand-fields { grid-template-columns: 1fr; }
        }
    </style>

    <div class="card">
        <h3 style="margin-top:0;">Brand Settings</h3>
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div class="brand-fields">
                    <div>
                        <label for="site_name">Store Name</label>
                        <input id="site_name" type="text" name="site_name" value="{{ old('site_name', $settings->site_name) }}" required>
                    </div>
                    <div>
                        <label for="site_tagline">Tagline</label>
                        <input id="site_tagline" type="text" name="site_tagline" value="{{ old('site_tagline', $settings->site_tagline) }}">
                    </div>
                    <div class="full">
                        <label for="footer_text">Footer Text</label>
                        <textarea id="footer_text" name="footer_text">{{ old('footer_text', $settings->footer_text) }}</textarea>
                    </div>
                    <div class="full">
                        <label for="logo">Logo</label>
                        <input id="logo" type="file" name="logo" accept="image/*">
                        @if(!empty($logoPreview))
                            <img src="{{ $logoPreview }}" alt="{{ $settings->site_name }}" class="preview-logo">
                        @endif
                    </div>
                    <div class="full">
                        <button type="submit">Save settings</button>
                    </div>
                </div>
                <div class="footer-preview">
                    <h4>Preview Footer</h4>
                    <div id="footer-preview-text">{{ $footerPreview }}</div>
                </div>
            </div>
        </form>
    </div>
    <script>
        (function () {
            const input = document.getElementById('footer_text');
            const preview = document.getElementById('footer-preview-text');
            const nameInput = document.getElementById('site_name');
            if (!input || !preview) return;
            function render() {
                const custom = (input.value || '').trim();
                const name = (nameInput && nameInput.value ? nameInput.value : 'Store').trim();
                preview.textContent = custom || ('© {{ now()->year }} ' + name + '. All rights reserved.');
            }
            input.addEventListener('input', render);
            if (nameInput) nameInput.addEventListener('input', render);
        })();
    </script>
@endsection
