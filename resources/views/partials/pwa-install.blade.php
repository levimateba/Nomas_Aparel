@if(config('pwa.enabled', true))
@php
    $pwaContext = $pwaContext ?? 'storefront';
    $pwaIsAdmin = $pwaContext === 'admin';
    $pwaSuffix = $pwaIsAdmin ? '-admin' : '';
    $pwaStorageKey = $pwaIsAdmin ? 'pwa-admin-install-dismissed-at' : 'pwa-install-dismissed-at';
    $pwaScope = $pwaIsAdmin ? '/admin/' : '/';
    $pwaSiteName = $settings->site_name ?? ($pwaIsAdmin ? 'Staff App' : 'our app');
    $pwaTitle = $pwaIsAdmin ? 'Install '.$pwaSiteName.' POS' : 'Install '.$pwaSiteName;
    $pwaText = $pwaIsAdmin
        ? 'Add the POS app to your tablet home screen for faster checkout.'
        : 'Add to your home screen for quick access while shopping.';
    $pwaBannerIcon = url('/pwa/icon-192.png').'?v='.config('pwa.cache_version', '1');
@endphp
<style>
    #pwa-install-banner{{ $pwaSuffix }} {
        position: fixed;
        z-index: 100000;
        display: none;
        flex-direction: column;
        gap: 10px;
        box-sizing: border-box;
        border-radius: 14px;
        padding: 12px 14px;
        /* Mobile default: near-full width */
        left: 12px;
        right: 12px;
        bottom: calc(12px + env(safe-area-inset-bottom, 0px));
        width: auto;
        max-width: none;
        @if($pwaIsAdmin)
        background: #0b1220;
        border: 1px solid rgba(212, 175, 55, 0.28);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
        @else
        background: #fff;
        border: 1px solid rgba(22, 69, 110, 0.12);
        box-shadow: 0 16px 40px rgba(22, 69, 110, 0.18);
        @endif
    }
    #pwa-install-banner{{ $pwaSuffix }}.is-visible { display: flex; }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-top {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        background: {{ $pwaIsAdmin ? '#d4af37' : '#16456e' }};
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-copy { flex: 1; min-width: 0; }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-copy strong {
        display: block;
        font-size: 13px;
        margin-bottom: 2px;
        color: {{ $pwaIsAdmin ? '#fbbf24' : '#16456e' }};
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-copy span {
        display: block;
        font-size: 11px;
        line-height: 1.35;
        color: {{ $pwaIsAdmin ? 'rgba(226,232,240,0.78)' : '#64748b' }};
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-install-btn {
        border: 0;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
        flex: 1 1 auto;
        @if($pwaIsAdmin)
        background: #d4af37;
        color: #111827;
        @else
        background: #165752;
        color: #fff;
        @endif
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-dismiss-btn {
        background: transparent;
        border: 0;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        padding: 10px 8px;
        white-space: nowrap;
        flex: 0 0 auto;
        color: {{ $pwaIsAdmin ? 'rgba(226,232,240,0.7)' : '#64748b' }};
    }
    /* Phones */
    @media (max-width: 1023.98px) {
        #pwa-install-banner{{ $pwaSuffix }} {
            bottom: calc(4.25rem + 16px + env(safe-area-inset-bottom, 0px));
        }
    }
    /* Laptop / desktop / dark mode: small bottom-right card */
    @media (min-width: 1024px) {
        #pwa-install-banner{{ $pwaSuffix }} {
            left: auto !important;
            right: 28px !important;
            bottom: calc(28px + env(safe-area-inset-bottom, 0px)) !important;
            width: 300px !important;
            max-width: 300px !important;
            padding: 12px !important;
        }
    }
    body:has(.pos-cart-dock) #pwa-install-banner{{ $pwaSuffix }} {
        bottom: calc(88px + env(safe-area-inset-bottom, 0px));
    }
    @media (min-width: 1024px) {
        body:has(.pos-cart-dock) #pwa-install-banner{{ $pwaSuffix }} {
            bottom: calc(28px + env(safe-area-inset-bottom, 0px)) !important;
        }
    }
</style>

<div id="pwa-install-banner{{ $pwaSuffix }}" role="dialog" aria-live="polite" aria-label="Install app">
    <div class="pwa-top">
        <img
            class="pwa-icon"
            src="{{ $pwaBannerIcon }}"
            alt="{{ $pwaSiteName }}"
            width="48"
            height="48"
        >
        <div class="pwa-copy">
            <strong>{{ $pwaTitle }}</strong>
            <span id="pwa-install-text{{ $pwaSuffix }}">{{ $pwaText }}</span>
        </div>
    </div>
    <div class="pwa-actions">
        <button type="button" class="pwa-install-btn" id="pwa-install-btn{{ $pwaSuffix }}">Install</button>
        <button type="button" class="pwa-dismiss-btn" id="pwa-dismiss-btn{{ $pwaSuffix }}">Not now</button>
    </div>
</div>

<script>
(function () {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    const suffix = @json($pwaSuffix);
    const banner = document.getElementById('pwa-install-banner' + suffix);
    const installBtn = document.getElementById('pwa-install-btn' + suffix);
    const dismissBtn = document.getElementById('pwa-dismiss-btn' + suffix);
    const installText = document.getElementById('pwa-install-text' + suffix);
    const storageKey = @json($pwaStorageKey);
    const swScope = @json($pwaScope);
    const dismissDays = 14;
    let deferredPrompt = null;
    let mode = 'android';

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    }

    function wasDismissedRecently() {
        const raw = localStorage.getItem(storageKey);
        if (!raw) {
            return false;
        }
        const dismissedAt = Number(raw);
        if (!Number.isFinite(dismissedAt)) {
            return false;
        }
        return (Date.now() - dismissedAt) < (dismissDays * 24 * 60 * 60 * 1000);
    }

    function showBanner(nextMode) {
        if (!banner || isStandalone() || wasDismissedRecently()) {
            return;
        }

        mode = nextMode || mode;

        if (mode === 'ios') {
            installText.textContent = 'Tap Share, then “Add to Home Screen”.';
            installBtn.textContent = 'How to install';
        } else if (mode === 'manual') {
            installText.textContent = 'Open browser menu → “Add to Home screen” / “Install app”.';
            installBtn.textContent = 'How to install';
        } else {
            installBtn.textContent = 'Install';
        }

        banner.classList.add('is-visible');
    }

    function hideBanner() {
        banner?.classList.remove('is-visible');
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        showBanner('android');
    });

    installBtn?.addEventListener('click', async function () {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            await deferredPrompt.userChoice;
            deferredPrompt = null;
            hideBanner();
            return;
        }

        if (mode === 'ios' || /iPad|iPhone|iPod/.test(navigator.userAgent)) {
            alert('Tap the Share button in Safari, then choose “Add to Home Screen”.');
            return;
        }

        alert('Open your browser menu and choose “Install app” or “Add to Home screen”.');
    });

    dismissBtn?.addEventListener('click', function () {
        localStorage.setItem(storageKey, String(Date.now()));
        hideBanner();
    });

    navigator.serviceWorker.register(@json(url('/sw.js')), { scope: swScope }).catch(function () {});

    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isMobile = /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);

    if (!isStandalone() && !wasDismissedRecently()) {
        window.setTimeout(function () {
            if (deferredPrompt) {
                showBanner('android');
                return;
            }
            if (isIos) {
                showBanner('ios');
            } else if (isMobile) {
                showBanner('manual');
            }
        }, 900);
    }
})();
</script>
@endif
