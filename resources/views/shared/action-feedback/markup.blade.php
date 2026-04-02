@php
    $actionFeedbackConfig = $actionFeedbackConfig ?? [];
    $confirmConfig = array_merge([
        'modalId' => 'actionFeedbackConfirmModal',
        'iconId' => 'actionFeedbackConfirmIcon',
        'titleId' => 'actionFeedbackConfirmTitle',
        'messageId' => 'actionFeedbackConfirmMessage',
        'detailId' => 'actionFeedbackConfirmDetail',
        'submitButtonId' => 'actionFeedbackConfirmSubmitBtn',
        'cancelButtonId' => null,
        'cancelLabel' => 'Cancel',
        'confirmLabel' => 'Continue',
        'defaultTitle' => 'Confirm Action',
        'defaultMessage' => 'Are you sure you want to continue?',
    ], $actionFeedbackConfig['confirm'] ?? []);

    $toastConfig = array_merge([
        'containerId' => 'actionFeedbackToastContainer',
        'liveRegionId' => 'actionFeedbackLiveRegion',
    ], $actionFeedbackConfig['toast'] ?? []);
@endphp

<div id="{{ $confirmConfig['modalId'] }}" class="action-feedback-confirm-overlay" aria-hidden="true">
    <div
        class="action-feedback-confirm-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $confirmConfig['titleId'] }}"
        aria-describedby="{{ $confirmConfig['messageId'] }}"
    >
        <div class="action-feedback-confirm-header">
            <div id="{{ $confirmConfig['iconId'] }}" class="action-feedback-confirm-icon primary" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4"></path>
                    <path d="M12 8h.01"></path>
                </svg>
            </div>
            <div class="action-feedback-confirm-copy">
                <h3 id="{{ $confirmConfig['titleId'] }}" class="action-feedback-confirm-title">{{ $confirmConfig['defaultTitle'] }}</h3>
                <p id="{{ $confirmConfig['messageId'] }}" class="action-feedback-confirm-message">{{ $confirmConfig['defaultMessage'] }}</p>
                <div id="{{ $confirmConfig['detailId'] }}" class="action-feedback-confirm-detail" hidden></div>
            </div>
        </div>
        <div class="action-feedback-confirm-actions">
            <button
                type="button"
                @if($confirmConfig['cancelButtonId']) id="{{ $confirmConfig['cancelButtonId'] }}" @endif
                class="action-feedback-confirm-btn secondary"
                data-modal-close="{{ $confirmConfig['modalId'] }}"
            >
                {{ $confirmConfig['cancelLabel'] }}
            </button>
            <button
                type="button"
                id="{{ $confirmConfig['submitButtonId'] }}"
                class="action-feedback-confirm-btn primary"
                data-default-label="{{ $confirmConfig['confirmLabel'] }}"
            >
                {{ $confirmConfig['confirmLabel'] }}
            </button>
        </div>
    </div>
</div>

<div id="{{ $toastConfig['containerId'] }}" class="action-feedback-toast-container" aria-live="polite" aria-atomic="true"></div>
<div id="{{ $toastConfig['liveRegionId'] }}" class="action-feedback-sr-only" aria-live="polite" aria-atomic="true"></div>
