<div class="pwa-offline-indicator" id="pwaOfflineIndicator" role="status" aria-live="polite" hidden>
    <span class="pwa-offline-dot" aria-hidden="true"></span>
    <span>Offline mode</span>
</div>

<section class="pwa-install-banner" id="pwaInstallPrompt" aria-label="Install app" hidden>
    <div class="pwa-install-icon" aria-hidden="true">
        <img src="{{ asset('pwa/icons/maskable-192x192.png') }}" alt="">
    </div>
    <div class="pwa-install-copy">
        <p class="pwa-install-title">Install Library Management</p>
        <p class="pwa-install-text">Open it from your home screen with a native app feel.</p>
    </div>
    <div class="pwa-install-actions">
        <button type="button" class="pwa-button" id="pwaInstallButton">Install</button>
        <button type="button" class="pwa-icon-button" id="pwaInstallDismissButton" aria-label="Dismiss install prompt">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</section>

<section class="pwa-update-banner" id="pwaUpdatePrompt" aria-label="App update available" hidden>
    <div class="pwa-update-icon" aria-hidden="true">
        <i data-lucide="refresh-cw"></i>
    </div>
    <div class="pwa-update-copy">
        <p class="pwa-update-title">Update ready</p>
        <p class="pwa-update-text">Refresh once to use the latest app shell.</p>
    </div>
    <div class="pwa-update-actions">
        <button type="button" class="pwa-button" id="pwaUpdateButton">Refresh</button>
        <button type="button" class="pwa-icon-button" id="pwaUpdateDismissButton" aria-label="Dismiss update prompt">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</section>

<script src="{{ asset('pwa/pwa.js') }}" defer></script>
