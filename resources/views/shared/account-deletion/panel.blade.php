@php
    $state = $accountDeletionState ?? [];
    $status = $state['status'] ?? 'allowed';
    $title = $state['title'] ?? 'Danger Zone';
    $message = $state['message'] ?? 'Deleting your account is permanent.';
    $detail = $state['detail'] ?? 'All your data will be removed and cannot be recovered.';
    $buttonLabel = $state['button_label'] ?? 'Delete Account';
    $buttonTooltip = $state['button_tooltip'] ?? 'This action cannot be undone';
    $buttonEnabled = (bool) ($state['button_enabled'] ?? true);
    $allowed = (bool) ($state['allowed'] ?? false);
    $isAdminBlocked = $status === 'blocked_admin';
    $warningVariant = $allowed ? 'default' : 'restricted';
    $warningTitle = $allowed ? 'Permanent account deletion' : 'Account deletion is currently unavailable';
    $modalHeading = $allowed ? 'Confirm Account Deletion' : 'Account Deletion Notice';
    $modalPrompt = $allowed
        ? 'Type DELETE in the field below to confirm. This action cannot be undone.'
        : $message;
@endphp

<div
    class="account-deletion-zone"
    data-account-deletion-root
    data-status="{{ $status }}"
    data-delete-url="{{ route('account.delete') }}"
    data-redirect-url="{{ url('/') }}"
    data-redirect-delay="3000"
    data-allowed="{{ $allowed ? 'true' : 'false' }}"
    data-admin-blocked="{{ $isAdminBlocked ? 'true' : 'false' }}"
>
    <div class="account-deletion-zone__header">
        <div class="account-deletion-zone__lead">
            <div class="account-deletion-zone__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                </svg>
            </div>
            <div>
                <h3 class="account-deletion-zone__title">{{ $title }}</h3>
                <p class="account-deletion-zone__subtitle">Deleting your account is permanent. All your data will be removed and cannot be recovered.</p>
            </div>
        </div>
        <span class="account-deletion-zone__status">{{ $allowed ? 'Action ready' : 'Action restricted' }}</span>
    </div>

    <div class="account-deletion-zone__warning {{ $warningVariant === 'restricted' ? 'is-restricted' : '' }}">
        <p class="account-deletion-zone__warning-title">{{ $warningTitle }}</p>
        <p class="account-deletion-zone__warning-copy">{{ $message }}</p>
        @if ($detail)
            <p class="account-deletion-zone__warning-copy">{{ $detail }}</p>
        @endif
    </div>

    @if (($state['issued_books_count'] ?? 0) > 0 || ($state['pending_fines_count'] ?? 0) > 0)
        <div class="account-deletion-zone__meta">
            @if (($state['issued_books_count'] ?? 0) > 0)
                <span class="account-deletion-zone__pill">
                    <strong>{{ $state['issued_books_count'] }}</strong> issued {{ $state['issued_books_count'] === 1 ? 'book' : 'books' }}
                </span>
            @endif

            @if (($state['pending_fines_count'] ?? 0) > 0)
                <span class="account-deletion-zone__pill">
                    <strong>{{ $state['pending_fines_count'] }}</strong> pending {{ $state['pending_fines_count'] === 1 ? 'fine' : 'fines' }}
                </span>
            @endif
        </div>
    @endif

    <div class="account-deletion-zone__actions">
        <button
            type="button"
            class="account-deletion-zone__button {{ $buttonEnabled ? '' : 'is-disabled' }}"
            data-account-deletion-trigger
            {{ $buttonEnabled ? '' : 'disabled' }}
            title="{{ $buttonTooltip }}"
        >
            <span>{{ $buttonLabel }}</span>
        </button>
        <span class="account-deletion-zone__helper">{{ $buttonTooltip }}</span>
    </div>

    <div class="account-deletion-zone__feedback" data-account-deletion-feedback data-type="info" aria-live="polite">
        <p class="account-deletion-zone__feedback-title"></p>
        <p class="account-deletion-zone__feedback-message"></p>
        <p class="account-deletion-zone__feedback-detail"></p>
    </div>

    <div class="account-deletion-modal" data-account-deletion-modal hidden>
        <div class="account-deletion-modal__panel" role="dialog" aria-modal="true" aria-labelledby="accountDeletionModalTitle" aria-describedby="accountDeletionModalMessage">
            <div class="account-deletion-modal__header">
                <div class="account-deletion-modal__heading">
                    <div class="account-deletion-zone__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                            <path d="M12 9v4"></path>
                            <path d="M12 17h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 id="accountDeletionModalTitle">{{ $modalHeading }}</h3>
                        <p id="accountDeletionModalMessage" data-account-deletion-modal-message>{{ $modalPrompt }}</p>
                    </div>
                </div>
                <button type="button" class="account-deletion-modal__close" data-account-deletion-close aria-label="Close dialog">×</button>
            </div>

            <div class="account-deletion-modal__body">
                <div class="account-deletion-modal__prompt" data-account-deletion-modal-detail>
                    {{ $detail }}
                </div>

                <div class="account-deletion-modal__field" data-account-deletion-confirmation-group {{ $allowed ? '' : 'hidden' }}>
                    <label for="accountDeletionConfirmationInput">Type DELETE to confirm</label>
                    <input type="text" id="accountDeletionConfirmationInput" data-account-deletion-confirmation-input autocomplete="off" spellcheck="false">
                    <div class="account-deletion-modal__field-error" data-account-deletion-error aria-live="polite"></div>
                </div>
            </div>

            <div class="account-deletion-modal__footer">
                <button type="button" class="account-deletion-modal__secondary" data-account-deletion-close>Cancel</button>
                <button type="button" class="account-deletion-modal__primary" data-account-deletion-confirm {{ $allowed ? 'disabled' : '' }}>
                    <span data-account-deletion-confirm-label>{{ $allowed ? 'Delete Account' : 'Close' }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
