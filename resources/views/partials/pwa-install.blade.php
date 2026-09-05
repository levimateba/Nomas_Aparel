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
        left: 12px;
        right: 12px;
        bottom: 12px;
        z-index: 2000;
        display: none;
        border-radius: 16px;
        padding: 14px 16px;
        gap: 12px;
        align-items: center;
        @if($pwaIsAdmin)
        background: #121212;
        border: 1px solid rgba(212, 175, 55, 0.25);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
        @else
        background: #fff;
        border: 1px solid rgba(22, 69, 110, 0.12);
        box-shadow: 0 16px 40px rgba(22, 69, 110, 0.18);
        @endif
    }
    #pwa-install-banner{{ $pwaSuffix }}.is-visible { display: flex; }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        background: {{ $pwaIsAdmin ? '#d4af37' : '#16456e' }};
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-copy { flex: 1; min-width: 0; }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-copy strong {
        display: block;
        font-size: 14px;
        margin-bottom: 2px;
        color: {{ $pwaIsAdmin ? '#d4af37' : '#16456e' }};
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-copy span {
        display: block;
        font-size: 12px;
        line-height: 1.4;
        color: {{ $pwaIsAdmin ? 'rgba(255,255,255,0.72)' : '#64748b' }};
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex-shrink: 0;
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-install-btn {
        border: 0;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        @if($pwaIsAdmin)
        background: #d4af37;
        color: #121212;
        @else
        background: #165752;
        color: #fff;
        @endif
    }
    #pwa-install-banner{{ $pwaSuffix }} .pwa-dismiss-btn {
        background: transparent;
        border: 0;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        padding: 2px 0;
        color: {{ $pwaIsAdmin ? 'rgba(255,255,255,0.55)' : '#64748b' }};
    }
    @media (max-width: 575.98px) {
        #pwa-install-banner{{ $pwaSuffix }} {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }
        #pwa-install-banner{{ $pwaSuffix }} .pwa-icon { margin: 0 auto; }
        #pwa-install-banner{{ $pwaSuffix }} .pwa-actions { flex-direction: row; justify-content: center; }
    }
</style>

<div id="pwa-install-banner{{ $pwaSuffix }}" role="dialog" aria-live="polite" aria-label="Install app">
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
    <div class="pwa-actions">
        <button type="button" class="pwa-install-btn" id="pwa-install-btn{{ $pwaSuffix }}">Install App</button>
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

    function showBanner(mode) {
        if (!banner || isStandalone() || wasDismissedRecently()) {
            return;
        }

        if (mode === 'ios') {
            installText.textContent = 'Tap Share, then "Add to Home Screen" to install the app.';
            installBtn.textContent = 'How to install';
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

        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            alert('Tap the Share button in Safari, then choose "Add to Home Screen".');
        }
    });

    dismissBtn?.addEventListener('click', function () {
        localStorage.setItem(storageKey, String(Date.now()));
        hideBanner();
    });

    navigator.serviceWorker.register(@json(url('/sw.js')), { scope: swScope }).catch(function () {});

    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (isIos && !isStandalone() && !wasDismissedRecently()) {
        window.setTimeout(function () {
            if (!deferredPrompt) {
                showBanner('ios');
            }
        }, 4000);
    }
})();
</script>
@endif
