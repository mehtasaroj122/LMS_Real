<style>
    .account-deletion-zone {
        --account-delete-danger: #dc2626;
        --account-delete-danger-strong: #b91c1c;
        --account-delete-danger-soft: rgba(220, 38, 38, 0.1);
        --account-delete-danger-border: rgba(220, 38, 38, 0.22);
        --account-delete-warning: #d97706;
        --account-delete-warning-soft: rgba(217, 119, 6, 0.12);
        --account-delete-warning-border: rgba(217, 119, 6, 0.22);
        --account-delete-text: #111827;
        --account-delete-muted: #6b7280;
        --account-delete-surface: #ffffff;
        --account-delete-surface-muted: #fff7f7;
        --account-delete-border: #e5e7eb;
        --account-delete-disabled: #9ca3af;
        margin-top: 0;
        padding: 1.15rem;
        border-radius: 1rem;
        border: 1px solid var(--account-delete-danger-border);
        background:
            radial-gradient(circle at top right, rgba(220, 38, 38, 0.08), transparent 42%),
            linear-gradient(180deg, var(--account-delete-surface-muted), var(--account-delete-surface));
        color: var(--account-delete-text);
    }

    body.dark-theme .account-deletion-zone {
        --account-delete-danger-soft: rgba(248, 113, 113, 0.14);
        --account-delete-danger-border: rgba(248, 113, 113, 0.24);
        --account-delete-warning-soft: rgba(251, 191, 36, 0.16);
        --account-delete-warning-border: rgba(251, 191, 36, 0.28);
        --account-delete-text: #f8fafc;
        --account-delete-muted: #cbd5e1;
        --account-delete-surface: #0f172a;
        --account-delete-surface-muted: #1f1720;
        --account-delete-border: #334155;
        --account-delete-disabled: #64748b;
    }

    .account-deletion-zone__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .account-deletion-zone__lead {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
        min-width: 0;
    }

    .account-deletion-zone__icon {
        width: 2.6rem;
        height: 2.6rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.85rem;
        background: var(--account-delete-danger-soft);
        color: var(--account-delete-danger);
        flex-shrink: 0;
    }

    .account-deletion-zone__icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .account-deletion-zone__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: inherit;
    }

    .account-deletion-zone__subtitle {
        margin: 0.22rem 0 0;
        font-size: 0.84rem;
        line-height: 1.55;
        color: var(--account-delete-muted);
    }

    .account-deletion-zone__status {
        display: inline-flex;
        align-items: center;
        padding: 0.42rem 0.75rem;
        border-radius: 999px;
        background: var(--account-delete-danger-soft);
        color: var(--account-delete-danger);
        font-size: 0.74rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .account-deletion-zone__warning {
        display: grid;
        gap: 0.35rem;
        margin-bottom: 1rem;
        padding: 0.95rem 1rem;
        border-radius: 0.9rem;
        border: 1px solid var(--account-delete-danger-border);
        background: var(--account-delete-danger-soft);
    }

    .account-deletion-zone__warning.is-restricted {
        border-color: var(--account-delete-warning-border);
        background: var(--account-delete-warning-soft);
    }

    .account-deletion-zone__warning-title {
        margin: 0;
        font-size: 0.86rem;
        font-weight: 700;
        color: inherit;
    }

    .account-deletion-zone__warning-copy {
        margin: 0;
        font-size: 0.82rem;
        line-height: 1.55;
        color: var(--account-delete-muted);
    }

    .account-deletion-zone__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-bottom: 1rem;
    }

    .account-deletion-zone__pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.72rem;
        border-radius: 999px;
        border: 1px solid var(--account-delete-border);
        background: rgba(255, 255, 255, 0.55);
        font-size: 0.78rem;
        color: var(--account-delete-muted);
    }

    body.dark-theme .account-deletion-zone__pill {
        background: rgba(15, 23, 42, 0.7);
    }

    .account-deletion-zone__pill strong {
        color: inherit;
    }

    .account-deletion-zone__actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .account-deletion-zone__helper {
        font-size: 0.78rem;
        color: var(--account-delete-muted);
    }

    .account-deletion-zone__button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-width: 190px;
        padding: 0.78rem 1.15rem;
        border: 1px solid transparent;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, var(--account-delete-danger), var(--account-delete-danger-strong));
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.22s ease, box-shadow 0.22s ease, opacity 0.22s ease;
        box-shadow: 0 16px 28px -22px rgba(185, 28, 28, 0.8);
    }

    .account-deletion-zone__button:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 18px 32px -22px rgba(185, 28, 28, 0.9);
    }

    .account-deletion-zone__button:disabled,
    .account-deletion-zone__button.is-disabled {
        background: var(--account-delete-disabled);
        color: #f8fafc;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .account-deletion-zone__button-spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.35);
        border-top-color: currentColor;
        border-radius: 999px;
        animation: accountDeletionSpin 0.8s linear infinite;
    }

    .account-deletion-zone__feedback {
        display: none;
        gap: 0.4rem;
        margin-top: 1rem;
        padding: 0.9rem 1rem;
        border-radius: 0.9rem;
        border: 1px solid var(--account-delete-border);
        background: rgba(255, 255, 255, 0.68);
    }

    .account-deletion-zone__feedback.is-visible {
        display: grid;
        animation: accountDeletionSlideIn 180ms ease-out;
    }

    body.dark-theme .account-deletion-zone__feedback {
        background: rgba(15, 23, 42, 0.72);
    }

    .account-deletion-zone__feedback[data-type="success"] {
        border-color: rgba(16, 185, 129, 0.24);
        background: rgba(16, 185, 129, 0.08);
    }

    .account-deletion-zone__feedback[data-type="warning"] {
        border-color: var(--account-delete-warning-border);
        background: var(--account-delete-warning-soft);
    }

    .account-deletion-zone__feedback[data-type="error"] {
        border-color: var(--account-delete-danger-border);
        background: var(--account-delete-danger-soft);
    }

    .account-deletion-zone__feedback-title {
        margin: 0;
        font-size: 0.84rem;
        font-weight: 700;
        color: inherit;
    }

    .account-deletion-zone__feedback-message,
    .account-deletion-zone__feedback-detail {
        margin: 0;
        font-size: 0.8rem;
        line-height: 1.55;
        color: var(--account-delete-muted);
    }

    .account-deletion-modal[hidden] {
        display: none !important;
    }

    .account-deletion-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1400;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.62);
        animation: accountDeletionFadeIn 180ms ease-out;
    }

    .account-deletion-modal__panel {
        width: min(520px, 100%);
        border-radius: 1.1rem;
        border: 1px solid var(--account-delete-danger-border);
        background: var(--account-delete-surface);
        color: var(--account-delete-text);
        box-shadow: 0 30px 60px -28px rgba(15, 23, 42, 0.7);
        transform-origin: center top;
        animation: accountDeletionPopIn 220ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .account-deletion-modal__header,
    .account-deletion-modal__body,
    .account-deletion-modal__footer {
        padding: 1rem 1.1rem;
    }

    .account-deletion-modal__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        border-bottom: 1px solid var(--account-delete-border);
    }

    .account-deletion-modal__heading {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
        min-width: 0;
    }

    .account-deletion-modal__heading h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
    }

    .account-deletion-modal__heading p {
        margin: 0.24rem 0 0;
        font-size: 0.84rem;
        line-height: 1.55;
        color: var(--account-delete-muted);
    }

    .account-deletion-modal__close {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 999px;
        background: transparent;
        color: var(--account-delete-muted);
        cursor: pointer;
    }

    .account-deletion-modal__close:hover {
        background: rgba(148, 163, 184, 0.12);
        color: var(--account-delete-text);
    }

    .account-deletion-modal__body {
        display: grid;
        gap: 0.9rem;
    }

    .account-deletion-modal__prompt {
        padding: 0.9rem 1rem;
        border-radius: 0.9rem;
        border: 1px solid var(--account-delete-danger-border);
        background: var(--account-delete-danger-soft);
        font-size: 0.82rem;
        line-height: 1.55;
        color: var(--account-delete-text);
    }

    .account-deletion-modal__field label {
        display: block;
        margin-bottom: 0.45rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: inherit;
    }

    .account-deletion-modal__field input {
        width: 100%;
        padding: 0.78rem 0.9rem;
        border: 1px solid var(--account-delete-border);
        border-radius: 0.8rem;
        background: var(--account-delete-surface);
        color: var(--account-delete-text);
        font-size: 0.92rem;
        font-weight: 700;
        letter-spacing: 0.08em;
    }

    .account-deletion-modal__field input:focus {
        outline: none;
        border-color: var(--account-delete-danger);
        box-shadow: 0 0 0 4px var(--account-delete-danger-soft);
    }

    .account-deletion-modal__field-error {
        min-height: 1rem;
        margin-top: 0.45rem;
        font-size: 0.76rem;
        color: var(--account-delete-danger);
    }

    .account-deletion-modal__footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        border-top: 1px solid var(--account-delete-border);
    }

    .account-deletion-modal__secondary,
    .account-deletion-modal__primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-width: 130px;
        padding: 0.72rem 1rem;
        border-radius: 0.8rem;
        font-weight: 700;
        font-size: 0.86rem;
        cursor: pointer;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .account-deletion-modal__secondary {
        border: 1px solid var(--account-delete-border);
        background: transparent;
        color: var(--account-delete-text);
    }

    .account-deletion-modal__primary {
        border: 1px solid transparent;
        background: linear-gradient(135deg, var(--account-delete-danger), var(--account-delete-danger-strong));
        color: #ffffff;
    }

    .account-deletion-modal__primary:disabled {
        background: var(--account-delete-disabled);
        cursor: not-allowed;
    }

    @keyframes accountDeletionSpin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes accountDeletionFadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes accountDeletionPopIn {
        from {
            opacity: 0;
            transform: translateY(16px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes accountDeletionSlideIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 640px) {
        .account-deletion-zone__header,
        .account-deletion-zone__actions,
        .account-deletion-modal__footer {
            flex-direction: column;
            align-items: stretch;
        }

        .account-deletion-zone__button,
        .account-deletion-modal__secondary,
        .account-deletion-modal__primary {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .account-deletion-zone__button,
        .account-deletion-zone__feedback,
        .account-deletion-modal,
        .account-deletion-modal__panel {
            transition: none !important;
            animation: none !important;
        }
    }
</style>
