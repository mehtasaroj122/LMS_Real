@unless ($supportsLiveMonitoring)
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-circle-info"></i>
        <div>
            <strong>Live monitoring is unavailable for the current cache driver.</strong>
            <p class="lock-alert-copy">This view can inspect active lock timers only when the cache store is set to `database`.</p>
        </div>
    </div>
@endunless
