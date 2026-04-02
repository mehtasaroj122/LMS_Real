@once
    <style>
        .action-feedback-sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .action-feedback-toast-container {
            position: fixed;
            top: 88px;
            right: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: min(360px, calc(100vw - 32px));
            z-index: 2100;
            pointer-events: none;
        }

        .action-feedback-toast {
            --action-feedback-toast-duration: 4200ms;
            position: relative;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 14px;
            padding: 16px 18px 18px;
            border-radius: 18px;
            overflow: hidden;
            pointer-events: auto;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(12px);
            color: #ffffff;
            animation: actionFeedbackToastIn 0.24s ease;
        }

        .action-feedback-toast.is-leaving {
            animation: actionFeedbackToastOut 0.18s ease forwards;
        }

        .action-feedback-toast.success {
            background: linear-gradient(135deg, rgba(22, 163, 74, 0.96), rgba(5, 150, 105, 0.94));
        }

        .action-feedback-toast.error {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.97), rgba(190, 24, 93, 0.94));
        }

        .action-feedback-toast.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.97), rgba(217, 119, 6, 0.94));
        }

        .action-feedback-toast.info {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.97), rgba(79, 70, 229, 0.94));
        }

        .action-feedback-toast-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.14);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .action-feedback-toast-icon svg {
            width: 18px;
            height: 18px;
        }

        .action-feedback-toast-copy {
            min-width: 0;
        }

        .action-feedback-toast-title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.3;
        }

        .action-feedback-toast-message {
            margin-top: 2px;
            font-size: 13px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.96);
        }

        .action-feedback-toast-detail {
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.4;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.78);
        }

        .action-feedback-toast-close {
            appearance: none;
            border: 0;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .action-feedback-toast-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .action-feedback-toast-close svg {
            width: 14px;
            height: 14px;
        }

        .action-feedback-toast-progress {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.18);
        }

        .action-feedback-toast-progress::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.92);
            transform-origin: left center;
            animation: actionFeedbackToastProgress var(--action-feedback-toast-duration) linear forwards;
        }

        .action-feedback-confirm-overlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(8px);
            z-index: 2050;
        }

        .action-feedback-confirm-overlay.is-open {
            display: flex;
        }

        .action-feedback-confirm-card {
            width: min(440px, 100%);
            padding: 24px;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.97);
            color: #0f172a;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.26);
            animation: actionFeedbackModalIn 0.3s ease;
        }

        body.dark-theme .action-feedback-confirm-card {
            background: rgba(15, 23, 42, 0.96);
            border-color: rgba(71, 85, 105, 0.88);
            color: #e2e8f0;
        }

        .action-feedback-confirm-header {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .action-feedback-confirm-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .action-feedback-confirm-icon svg {
            width: 22px;
            height: 22px;
        }

        .action-feedback-confirm-icon.primary,
        .action-feedback-confirm-icon.info {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
        }

        body.dark-theme .action-feedback-confirm-icon.primary,
        body.dark-theme .action-feedback-confirm-icon.info {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.85), rgba(30, 64, 175, 0.55));
            color: #bfdbfe;
        }

        .action-feedback-confirm-icon.success {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #15803d;
        }

        body.dark-theme .action-feedback-confirm-icon.success {
            background: linear-gradient(135deg, rgba(20, 83, 45, 0.86), rgba(22, 101, 52, 0.58));
            color: #86efac;
        }

        .action-feedback-confirm-icon.warning {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #b45309;
        }

        body.dark-theme .action-feedback-confirm-icon.warning {
            background: linear-gradient(135deg, rgba(120, 53, 15, 0.86), rgba(146, 64, 14, 0.58));
            color: #fcd34d;
        }

        .action-feedback-confirm-icon.danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #b91c1c;
        }

        body.dark-theme .action-feedback-confirm-icon.danger {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.85), rgba(127, 29, 29, 0.55));
            color: #fca5a5;
        }

        .action-feedback-confirm-copy {
            min-width: 0;
        }

        .action-feedback-confirm-title {
            margin: 2px 0 6px;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.3;
        }

        .action-feedback-confirm-message {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }

        body.dark-theme .action-feedback-confirm-message {
            color: #94a3b8;
        }

        .action-feedback-confirm-detail {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            background: #f8fafc;
            color: #475569;
        }

        body.dark-theme .action-feedback-confirm-detail {
            background: rgba(30, 41, 59, 0.85);
            color: #cbd5e1;
        }

        .action-feedback-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
        }

        .action-feedback-confirm-btn {
            appearance: none;
            border: 1px solid transparent;
            border-radius: 12px;
            min-width: 140px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .action-feedback-confirm-btn:hover {
            transform: translateY(-1px);
        }

        .action-feedback-confirm-btn.secondary {
            background: transparent;
            border-color: #cbd5e1;
            color: #334155;
        }

        body.dark-theme .action-feedback-confirm-btn.secondary {
            border-color: #475569;
            color: #e2e8f0;
        }

        .action-feedback-confirm-btn.primary,
        .action-feedback-confirm-btn.info {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.24);
        }

        .action-feedback-confirm-btn.success {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(22, 163, 74, 0.24);
        }

        .action-feedback-confirm-btn.warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(245, 158, 11, 0.24);
        }

        .action-feedback-confirm-btn.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.24);
        }

        .action-feedback-confirm-btn:disabled {
            opacity: 0.78;
            cursor: wait;
            transform: none;
        }

        @keyframes actionFeedbackToastIn {
            from {
                opacity: 0;
                transform: translate3d(20px, -8px, 0) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        @keyframes actionFeedbackToastOut {
            from {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }

            to {
                opacity: 0;
                transform: translate3d(18px, -4px, 0) scale(0.98);
            }
        }

        @keyframes actionFeedbackToastProgress {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        @keyframes actionFeedbackModalIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 768px) {
            .action-feedback-toast-container {
                top: 76px;
                right: 16px;
                left: 16px;
                width: auto;
            }

            .action-feedback-confirm-card {
                padding: 22px;
            }

            .action-feedback-confirm-actions {
                flex-wrap: wrap;
            }

            .action-feedback-confirm-btn {
                width: 100%;
            }
        }
    </style>
@endonce
