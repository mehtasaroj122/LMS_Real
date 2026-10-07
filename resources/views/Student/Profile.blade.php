@extends('Student.layouts.app')

@section('title', 'My Profile')

@push('styles')
    @include('shared.account-deletion.styles')
    <style>
        /* Profile Page Specific Styles */
        .profile-card {
            border-radius: 1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .profile-container {
            align-items: stretch;
        }

        .profile-sidebar-column,
        .profile-main-column {
            min-width: 0;
        }

        .profile-sidebar-column {
            align-self: stretch;
            position: relative;
        }

        .profile-sticky-card {
            position: -webkit-sticky;
            position: sticky;
            top: 1rem;
            overflow: visible;
            z-index: 2;
        }

        body.light-theme .profile-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme .profile-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #e5e7f0;
        }

        .profile-avatar {
            width: 128px;
            height: 128px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar-initial {
            color: white;
            font-size: 2.25rem;
            font-weight: bold;
        }

        .profile-avatar-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 36px;
            height: 36px;
            background-color: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid;
        }

        body.light-theme .profile-avatar-upload {
            border-color: #ffffff;
        }

        body.dark-theme .profile-avatar-upload {
            border-color: #1e293b;
        }

        .profile-avatar-upload:hover {
            background-color: #2563eb;
        }

        .profile-detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .account-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-auto-rows: 1fr;
            gap: 0.75rem;
        }

        .account-stat-card {
            --stat-accent: #2563eb;
            --stat-accent-dark: #60a5fa;
            --stat-soft: #eff6ff;
            --stat-soft-dark: rgba(37, 99, 235, 0.16);
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
            min-height: 6rem;
            height: 100%;
            padding: 0.875rem;
            position: relative;
            color: inherit;
            text-decoration: none;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease,
                background-color 180ms ease;
        }

        .account-stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--stat-accent);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
            background: #f8fafc;
        }

        .account-stat-card:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.24);
            outline-offset: 2px;
            border-color: var(--stat-accent);
        }

        .account-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            color: var(--stat-accent);
            background: var(--stat-soft);
            border-radius: 0.625rem;
        }

        .account-stat-icon svg {
            width: 1rem;
            height: 1rem;
        }

        .account-stat-value {
            overflow: hidden;
            margin-top: 0.4rem;
            color: #0f172a;
            font-size: clamp(1.125rem, 1.8vw, 1.4rem);
            font-weight: 750;
            line-height: 1.15;
            letter-spacing: -0.025em;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .account-stat-label {
            padding-right: 2.5rem;
            color: #334155;
            font-size: 0.8rem;
            font-weight: 650;
            line-height: 1.3;
        }

        .account-stat-context {
            margin-top: 0.25rem;
            color: #64748b;
            font-size: 0.7rem;
            line-height: 1.3;
        }

        .account-stat-card--amber {
            --stat-accent: #b45309;
            --stat-accent-dark: #fbbf24;
            --stat-soft: #fff7ed;
            --stat-soft-dark: rgba(245, 158, 11, 0.15);
        }

        .account-stat-card--purple {
            --stat-accent: #7c3aed;
            --stat-accent-dark: #a78bfa;
            --stat-soft: #f5f3ff;
            --stat-soft-dark: rgba(124, 58, 237, 0.16);
        }

        .account-stat-card--red {
            --stat-accent: #dc2626;
            --stat-accent-dark: #f87171;
            --stat-soft: #fef2f2;
            --stat-soft-dark: rgba(220, 38, 38, 0.15);
        }

        .account-stat-card--green,
        .account-stat-card--clear {
            --stat-accent: #15803d;
            --stat-accent-dark: #4ade80;
            --stat-soft: #f0fdf4;
            --stat-soft-dark: rgba(22, 163, 74, 0.15);
        }

        .account-stat-card--teal {
            --stat-accent: #0f766e;
            --stat-accent-dark: #2dd4bf;
            --stat-soft: #f0fdfa;
            --stat-soft-dark: rgba(13, 148, 136, 0.15);
        }

        body.dark-theme .account-stat-card {
            background: #172033;
            border-color: #334155;
            box-shadow: none;
        }

        body.dark-theme .account-stat-card:hover {
            background: #1e293b;
            border-color: var(--stat-accent-dark);
            box-shadow: 0 8px 20px rgba(2, 6, 23, 0.28);
        }

        body.dark-theme .account-stat-icon {
            color: var(--stat-accent-dark);
            background: var(--stat-soft-dark);
        }

        body.dark-theme .account-stat-value {
            color: #f8fafc;
        }

        body.dark-theme .account-stat-label {
            color: #e2e8f0;
        }

        body.dark-theme .account-stat-context {
            color: #94a3b8;
        }

        .tab-button {
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }

        .tab-button.active {
            border-bottom-color: #3b82f6;
            color: #3b82f6;
        }

        body.dark-theme .tab-button.active {
            color: #60a5fa;
            border-bottom-color: #60a5fa;
        }

        .profile-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        body.light-theme .profile-input {
            background-color: #f9fafb;
            border-color: transparent;
            color: #0f172a;
        }

        body.dark-theme .profile-input {
            background-color: #1e293b;
            border-color: transparent;
            color: #e5e7f0;
        }

        body.light-theme .profile-input:enabled {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .profile-input:enabled {
            background-color: #0f172a;
            border-color: #334155;
        }

        .profile-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        .primary-button {
            padding: 0.5rem 1rem;
            background-color: #3b82f6;
            color: white;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .primary-button:hover {
            background-color: #2563eb;
        }

        body.dark-theme .primary-button {
            background-color: #1e40af;
        }

        body.dark-theme .primary-button:hover {
            background-color: #1e3a8a;
        }

        .secondary-button {
            padding: 0.5rem 1rem;
            border: 1px solid;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        body.light-theme .secondary-button {
            border-color: #e5e7eb;
            color: #374151;
        }

        body.light-theme .secondary-button:hover {
            background-color: #f9fafb;
        }

        body.dark-theme .secondary-button {
            border-color: #4b5563;
            color: #d1d5db;
        }

        body.dark-theme .secondary-button:hover {
            background-color: #374151;
        }

        .password-requirements {
            border-radius: 0.5rem;
            padding: 1rem;
            border: 1px solid;
        }

        body.light-theme .password-requirements {
            background-color: #f9fafb;
            border-color: #e5e7eb;
        }

        body.dark-theme .password-requirements {
            background-color: #1e293b;
            border-color: #374151;
        }

        .password-requirement-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .upload-photo-container {
            text-align: center;
            padding: 2rem 0;
        }

        .upload-preview {
            width: 192px;
            height: 192px;
            border-radius: 50%;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-preview-initial {
            color: white;
            font-size: 3.5rem;
            font-weight: bold;
        }

        .upload-instructions {
            background-color: #f9fafb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: left;
        }

        body.dark-theme .upload-instructions {
            background-color: #1e293b;
            border-color: #334155;
        }

        .upload-instructions ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-top: 0.5rem;
        }

        .upload-instructions li {
            margin-bottom: 0.5rem;
            color: #6b7280;
        }

        body.dark-theme .upload-instructions li {
            color: #9ca3af;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #3b82f6;
            color: white;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .file-input-label:hover {
            background-color: #2563eb;
        }

        .upload-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .compact-only {
            display: none;
        }

        @media (max-width: 1024px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 1024px) {
            .profile-page .profile-container {
                grid-template-columns: minmax(280px, 320px) minmax(0, 1fr);
            }

            .profile-page .profile-sidebar-column .profile-card {
                max-width: 320px;
                margin-right: auto;
            }
        }

        @media (max-width: 1023px) {
            .profile-sticky-card {
                position: static;
                overflow: visible;
            }
        }

        @media (max-width: 768px) {
            .account-stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .account-stats-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
                width: 100%;
            }

            .button-group button {
                width: 100%;
            }

            .upload-actions {
                flex-direction: column;
            }

            .upload-actions button {
                width: 100%;
            }

            .upload-preview {
                width: 160px;
                height: 160px;
            }

            .upload-preview-initial {
                font-size: 2.5rem;
            }
        }

        /* Compact-only overrides scoped to `.profile-page` (preserve colors & classes) */
        .profile-page .profile-card {
            padding: 0.75rem !important;
        }

        .profile-page h1.text-3xl {
            font-size: 1.5rem !important;
            line-height: 1.1 !important;
        }

        .profile-page .text-2xl {
            font-size: 1.25rem !important;
        }

        .profile-page h3.text-lg {
            font-size: 1rem !important;
            margin-bottom: 0.25rem !important;
        }

        .profile-page .text-sm,
        .profile-page .text-secondary {
            font-size: 0.85rem !important;
        }

        .profile-page .profile-avatar {
            width: 96px !important;
            height: 96px !important;
        }

        .profile-page .profile-avatar-initial {
            font-size: 1.5rem !important;
        }

        .profile-page .grid.gap-6 {
            gap: 0.75rem !important;
        }

        .profile-page .p-6 {
            padding: 0.75rem !important;
        }

        .profile-page .p-4 {
            padding: 0.6rem !important;
        }

        .profile-page .mb-8 {
            margin-bottom: 1rem !important;
        }

        .profile-page .compact-only {
            display: inline-block;
        }

        .profile-page .full-only {
            display: none;
        }

        #removePhotoBtn {
            transition: all 0.2s ease;
        }

        #removePhotoBtn:hover {
            background-color: rgba(239, 68, 68, 0.2) !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.15);
        }

        #removePhotoBtn:active {
            transform: translateY(0);
        }

        #removePhotoBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-input-wrapper .profile-input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-secondary);
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 10;
            pointer-events: auto;
            flex-shrink: 0;
        }

        .password-toggle:hover {
            color: var(--color-primary);
            transform: scale(1.1);
        }

        .password-toggle:active {
            transform: scale(0.95);
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
            pointer-events: none;
        }

        .password-toggle .hidden {
            display: none;
        }

        .student-settings-shell {
            min-width: 0;
            padding: 1rem !important;
        }

        .student-settings-shell .settings-card-inner {
            min-width: 0;
        }

        .student-settings-shell [hidden] {
            display: none !important;
        }

        .student-settings-shell .settings-tabs {
            display: flex;
            gap: 0.25rem;
            border-bottom: 1px solid;
            margin-bottom: 1.25rem;
        }

        body.light-theme .student-settings-shell .settings-tabs {
            border-color: #e5e7eb;
        }

        body.dark-theme .student-settings-shell .settings-tabs {
            border-color: #334155;
        }

        .student-settings-shell .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.1rem;
            background: transparent;
            border: none;
            border-radius: 0.5rem 0.5rem 0 0;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: color 0.24s ease, background-color 0.24s ease, border-color 0.24s ease, box-shadow 0.24s ease, transform 0.24s ease;
            position: relative;
            top: 1px;
        }

        body.light-theme .student-settings-shell .tab-btn {
            color: #64748b;
        }

        body.dark-theme .student-settings-shell .tab-btn {
            color: #94a3b8;
        }

        .student-settings-shell .tab-btn:hover {
            background-color: rgba(59, 130, 246, 0.05);
            transform: translateY(-1px);
        }

        body.dark-theme .student-settings-shell .tab-btn:hover {
            background-color: rgba(96, 165, 250, 0.05);
        }

        .student-settings-shell .tab-btn.active {
            background-color: #ffffff;
            color: #1e40af;
            border: 1px solid #e5e7eb;
            border-bottom-color: transparent;
        }

        body.dark-theme .student-settings-shell .tab-btn.active {
            background-color: #1e293b;
            color: #60a5fa;
            border-color: #334155;
            border-bottom-color: #1e293b;
            box-shadow: 0 -12px 28px rgba(2, 6, 23, 0.2);
        }

        .student-settings-shell .tab-btn svg {
            width: 0.95rem;
            height: 0.95rem;
        }

        .student-settings-shell .tab-content {
            display: none;
            opacity: 0;
            transform: translateY(14px) scale(0.985);
            transform-origin: top center;
        }

        .student-settings-shell .tab-content.active {
            display: block;
            animation: studentProfileTabReveal 320ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .student-settings-shell .section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }

        body.dark-theme .student-settings-shell .section-header {
            border-bottom-color: rgba(148, 163, 184, 0.16);
        }

        .student-settings-shell .section-header-main {
            flex: 1;
            min-width: 0;
        }

        .student-settings-shell .section-header h2 {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: inherit;
        }

        .student-settings-shell .section-header p {
            color: #64748b;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        body.dark-theme .student-settings-shell .section-header p {
            color: #94a3b8;
        }

        .student-settings-shell .section-header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .student-settings-shell .section-status {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        body.dark-theme .student-settings-shell .section-status {
            background: rgba(15, 23, 42, 0.72);
            color: #94a3b8;
        }

        .student-settings-shell .section-status.editing {
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
        }

        body.dark-theme .student-settings-shell .section-status.editing {
            background: rgba(96, 165, 250, 0.18);
            color: #93c5fd;
        }

        .student-settings-shell .settings-form {
            max-width: 100%;
        }

        .student-settings-shell .form-group {
            margin-bottom: 1.15rem;
        }

        .student-settings-shell .form-label {
            display: block;
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
            color: inherit;
        }

        .student-settings-shell .required::after {
            content: " *";
            color: #ef4444;
        }

        .student-settings-shell .form-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            min-height: 42px;
            line-height: 1.3;
        }

        body.light-theme .student-settings-shell .form-input {
            background-color: #ffffff;
            border-color: #d1d5db;
            color: #111827;
        }

        body.dark-theme .student-settings-shell .form-input {
            background-color: #0f172a;
            border-color: #475569;
            color: #f8fafc;
        }

        body.light-theme .student-settings-shell .form-input:disabled {
            background-color: #f8fafc;
            border-color: #e5e7eb;
            color: #475569;
            cursor: not-allowed;
        }

        body.light-theme .student-settings-shell .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme .student-settings-shell .form-input:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.1);
        }

        body.dark-theme .student-settings-shell .form-input:disabled {
            background-color: #1e293b;
            border-color: #475569;
            color: #cbd5e1;
            opacity: 0.7;
            cursor: not-allowed;
        }

        .student-settings-shell #department:disabled {
            cursor: default;
        }

        .student-settings-shell textarea.form-input {
            min-height: 90px;
            resize: vertical;
            line-height: 1.45;
        }

        .student-settings-shell .form-hint {
            font-size: 0.75rem;
            margin-top: 0.375rem;
            line-height: 1.3;
            color: #6b7280;
        }

        body.dark-theme .student-settings-shell .form-hint {
            color: #9ca3af;
        }

        .student-settings-shell .field-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.375rem;
        }

        .student-settings-shell .char-counter {
            font-size: 0.75rem;
            color: #64748b;
            white-space: nowrap;
        }

        body.dark-theme .student-settings-shell .char-counter {
            color: #94a3b8;
        }

        .student-settings-shell .char-counter.is-limit {
            color: #ef4444;
        }

        .student-settings-shell .field-error {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.375rem;
            display: none;
        }

        .student-settings-shell .field-error.visible {
            display: block;
        }

        .student-settings-shell .form-input.is-invalid {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .student-settings-shell .form-input.is-valid {
            border-color: #10b981 !important;
            background-color: #f0fdf4 !important;
        }

        body.dark-theme .student-settings-shell .form-input.is-invalid {
            background-color: rgba(127, 29, 29, 0.25) !important;
        }

        body.dark-theme .student-settings-shell .form-input.is-valid {
            background-color: rgba(6, 95, 70, 0.22) !important;
        }

        .student-settings-shell .form-input.validating {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24'%3E%3Cg fill='none' stroke='%233b82f6' stroke-linecap='round' stroke-width='2'%3E%3Cpath stroke-opacity='.25' d='M12 3a9 9 0 1 0 9 9'/%3E%3Cpath d='M21 12A9 9 0 0 0 12 3'%3E%3CanimateTransform attributeName='transform' attributeType='XML' dur='0.8s' from='0 12 12' repeatCount='indefinite' to='360 12 12' type='rotate'/%3E%3C/path%3E%3C/g%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 40px center;
            background-size: 18px 18px;
        }

        .student-settings-shell .btn {
            padding: 0.625rem 1.1rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            line-height: 1.3;
        }

        .student-settings-shell .btn svg {
            width: 0.9rem;
            height: 0.9rem;
        }

        .student-settings-shell .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .student-settings-shell .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .student-settings-shell .btn-primary:hover {
            background-color: #2563eb;
        }

        .student-settings-shell .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .student-settings-shell .btn-danger:hover {
            background-color: #dc2626;
        }

        .student-settings-shell .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
        }

        .student-settings-shell .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .student-settings-shell .btn-outline {
            background-color: transparent;
            border: 1px solid;
        }

        body.light-theme .student-settings-shell .btn-outline {
            border-color: #d1d5db;
            color: #374151;
        }

        body.dark-theme .student-settings-shell .btn-outline {
            border-color: #475569;
            color: #e5e7eb;
        }

        .student-settings-shell .btn-outline:hover {
            background-color: #f9fafb;
        }

        body.dark-theme .student-settings-shell .btn-outline:hover {
            background-color: #1e293b;
        }

        body.dark-theme .student-settings-shell .btn-secondary {
            background-color: #334155;
            color: #e5e7eb;
        }

        body.dark-theme .student-settings-shell .btn-secondary:hover {
            background-color: #475569;
        }

        .student-settings-shell .photo-upload-container,
        .student-settings-shell .security-section {
            padding: 0.25rem 0;
        }

        .student-settings-shell .photo-preview {
            width: 180px;
            height: 180px;
            border-radius: 0.75rem;
            margin: 0 auto 1.5rem;
            overflow: hidden;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        body.dark-theme .student-settings-shell .photo-preview {
            background-color: #334155;
        }

        .student-settings-shell .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-settings-shell .photo-upload-icon {
            color: #9ca3af;
        }

        body.dark-theme .student-settings-shell .photo-upload-icon {
            color: #cbd5e1;
        }

        .student-settings-shell .photo-upload-icon svg {
            width: 2.5rem;
            height: 2.5rem;
        }

        .student-settings-shell .file-input-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .student-settings-shell .file-input {
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

        .student-settings-shell .file-input-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.1rem;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            font-weight: 500;
            color: #1d4ed8;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .student-settings-shell .file-input-label:hover {
            background-color: #dbeafe;
            border-color: #93c5fd;
        }

        body.dark-theme .student-settings-shell .file-input-label {
            background-color: #334155;
            border: 1px solid #475569;
            color: #e5e7eb;
        }

        body.dark-theme .student-settings-shell .file-input-label:hover {
            background-color: #475569;
        }

        .student-settings-shell .file-input-wrapper:focus-within .file-input-label {
            outline: 2px solid rgba(59, 130, 246, 0.18);
            outline-offset: 2px;
        }

        .student-settings-shell #fileName {
            font-size: 0.875rem;
            color: #64748b;
        }

        body.dark-theme .student-settings-shell #fileName {
            color: #94a3b8;
        }

        .student-settings-shell .upload-requirements {
            max-width: 420px;
            margin: 0 auto;
            text-align: left;
        }

        .student-settings-shell .upload-requirements h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.375rem;
            color: inherit;
        }

        .student-settings-shell .upload-requirements p {
            font-size: 0.8125rem;
            line-height: 1.3;
            color: #64748b;
        }

        body.dark-theme .student-settings-shell .upload-requirements p {
            color: #94a3b8;
        }

        .student-settings-shell .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .student-settings-shell .password-intro {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.1rem;
            text-align: center;
            padding: 2.3rem 1rem 1rem;
        }

        .student-settings-shell .password-intro-icon {
            width: 78px;
            height: 78px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1e293b;
            color: #cbd5e1;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
        }

        .student-settings-shell .password-intro-icon svg {
            width: 1.9rem;
            height: 1.9rem;
        }

        body.dark-theme .student-settings-shell .password-intro-icon {
            background: #0f172a;
            color: #e2e8f0;
        }

        .student-settings-shell .password-intro-copy {
            max-width: 34rem;
            font-size: 1rem;
            line-height: 1.6;
            color: #64748b;
        }

        body.dark-theme .student-settings-shell .password-intro-copy {
            color: #94a3b8;
        }

        .student-settings-shell .password-intro-action {
            min-width: 210px;
            justify-content: center;
            padding: 0.9rem 1.65rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
        }

        .student-settings-shell .input-with-icon {
            position: relative;
        }

        .student-settings-shell .pwd-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            color: #64748b;
            font-size: 0.95rem;
            padding: 4px;
        }

        body.dark-theme .student-settings-shell .pwd-toggle {
            color: #cbd5e1;
        }

        .student-settings-shell .pwd-toggle svg {
            width: 1rem;
            height: 1rem;
        }

        .student-settings-shell .pwd-toggle:focus {
            outline: none;
        }

        .student-settings-shell .password-requirements {
            margin-top: 1.5rem;
            padding: 1.15rem;
            border-radius: 0.75rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .student-settings-shell .password-requirements {
            background-color: #1e293b;
            border-color: #334155;
        }

        .student-settings-shell .password-requirements h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.875rem;
            color: inherit;
        }

        .student-settings-shell .requirement-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .student-settings-shell .requirement-list li {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 0.625rem;
            font-size: 0.8125rem;
            line-height: 1.3;
            color: #475569;
        }

        body.dark-theme .student-settings-shell .requirement-list li {
            color: #cbd5e1;
        }

        .student-settings-shell .requirement-list li:last-child {
            margin-bottom: 0;
        }

        .student-settings-shell .requirement-icon {
            width: 0.95rem;
            height: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .student-settings-shell .requirement-list li.valid .requirement-icon {
            color: #10b981;
        }

        .student-settings-shell .requirement-list li.invalid .requirement-icon {
            color: #ef4444;
        }

        @keyframes studentProfileTabReveal {
            from {
                opacity: 0;
                transform: translateY(14px) scale(0.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .profile-card,
            .profile-avatar-upload,
            .account-stat-card,
            .student-settings-shell .tab-btn,
            .student-settings-shell .tab-content,
            .student-settings-shell .btn {
                transition: none !important;
                animation: none !important;
            }
        }

        @media (max-width: 640px) {
            .student-settings-shell {
                padding: 0.9rem !important;
            }

            .student-settings-shell .section-header {
                flex-direction: column;
            }

            .student-settings-shell .section-header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .student-settings-shell .field-meta {
                flex-direction: column;
                align-items: flex-start;
            }

            .student-settings-shell .action-buttons {
                flex-direction: column;
            }

            .student-settings-shell .action-buttons .btn,
            .student-settings-shell .section-header-actions .btn,
            .student-settings-shell .password-intro-action {
                width: 100%;
                justify-content: center;
            }

            .student-settings-shell .settings-tabs {
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $profilePhotoUrl = filled($user->profile_photo) 
            ? \App\Support\ProfilePhoto::resolveUrl($user->profile_photo) 
            : null;
        $departmentName = $student && $student->department ? $student->department->name : 'N/A';
        $studentIdentifier = $student ? ($student->roll_no ?: $student->student_id ?: 'N/A') : 'N/A';
        $usernameValue = $user->email ? explode('@', $user->email)[0] : 'student';
        $addressValue = $user->address ?? '';
        $initials = collect(preg_split('/\s+/', trim($user->name ?? '')) ?: [])
            ->filter()
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
        $initials = $initials !== '' ? $initials : 'ST';
    @endphp

    <div class="container px-1 py-1 mx-auto profile-page">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary">My Profile</h1>
            <p class="mt-2 text-secondary">View and manage your personal information and account settings</p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 profile-container">
            <!-- Left Column - User Profile Card -->
            <div class="profile-sidebar-column">
                <div class="p-6 profile-card profile-sticky-card">
                    <!-- Avatar -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="profile-avatar" id="leftProfileAvatar" data-default-initials="{{ $initials }}">
                            <img src="{{ $profilePhotoUrl ?? '' }}" alt="Profile Photo" id="leftAvatarImage" style="display: {{ $profilePhotoUrl ? 'block' : 'none' }};">
                            <span class="profile-avatar-initial" id="leftAvatarInitial" style="display: {{ $profilePhotoUrl ? 'none' : 'block' }};">{{ $initials }}</span>
                            <div class="profile-avatar-upload" onclick="switchToPhotoTab()" title="Upload Profile Photo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                            </div>
                        </div>
                        <h2 class="mb-1 text-xl font-bold text-primary" id="leftUserName">{{ $user->name }}</h2>
                        <p class="mb-3 text-secondary" id="leftUserEmail">{{ $user->email }}</p>
                        <span
                            class="px-4 py-1 text-sm font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                            Student
                        </span>
                    </div>

                    <!-- Divider -->
                    <div class="my-6 border-t border-border"></div>

                    <!-- User Details -->
                    <div class="space-y-4">
                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Username</p>
                                <p class="font-medium text-primary" id="leftUsernameValue">{{ $student ? $usernameValue : 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path
                                    d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-4 0v-9a2 2 0 0 1 2-2h2" />
                                <path d="M18 14h-8" />
                                <path d="M15 18h-5" />
                                <path d="M10 6h8v4h-8V6z" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Student ID</p>
                                <p class="font-medium text-primary">{{ $studentIdentifier }}</p>
                            </div>
                        </div>

                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                                <path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9" />
                                <path d="M12 3v6" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Department</p>
                                <p class="font-medium text-primary">{{ $departmentName }}</p>
                            </div>
                        </div>

                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Member Since</p>
                                <p class="font-medium text-primary">{{ $user->created_at ? $user->created_at->format('F d, Y') : 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Last Login</p>
                                <p class="font-medium text-primary">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="profile-main-column">
                <!-- Account Statistics -->
                <section class="p-6 mb-6 profile-card" aria-labelledby="account-statistics-heading">
                    <h2 class="mb-4 text-xl font-bold text-primary" id="account-statistics-heading">Account Statistics</h2>
                    <div class="account-stats-grid">
                        <!-- Books Issued -->
                        <a class="account-stat-card" href="{{ route('student.my-books') }}">
                            <span class="account-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                                </svg>
                            </span>
                            <span class="account-stat-label">Books Issued</span>
                            <span class="account-stat-value">{{ $booksIssuedCount }}</span>
                            <span class="account-stat-context">Currently borrowed</span>
                        </a>

                        <!-- Unpaid Fines -->
                        <a class="account-stat-card {{ $unpaidFinesCount > 0 ? 'account-stat-card--amber' : 'account-stat-card--clear' }}" href="{{ route('student.fines') }}">
                            <span class="account-stat-icon" aria-hidden="true">
                                @if ($unpaidFinesCount > 0)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10.3 2.86 1.82 17a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 2.86a2 2 0 0 0-3.4 0Z" />
                                        <path d="M12 9v4" />
                                        <path d="M12 17h.01" />
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 11 3 3L22 4" />
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                                    </svg>
                                @endif
                            </span>
                            <span class="account-stat-label">Unpaid Fines</span>
                            <span class="account-stat-value">{{ $unpaidFinesCount }}</span>
                            <span class="account-stat-context">{{ $unpaidFinesCount > 0 ? 'Requires attention' : 'No fines due' }}</span>
                        </a>

                        <!-- Pending Requests -->
                        <a class="account-stat-card {{ $pendingRequestsCount > 0 ? 'account-stat-card--purple' : 'account-stat-card--clear' }}" href="{{ route('student.requests') }}">
                            <span class="account-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>
                            <span class="account-stat-label">Pending Requests</span>
                            <span class="account-stat-value">{{ $pendingRequestsCount }}</span>
                            <span class="account-stat-context">{{ $pendingRequestsCount > 0 ? 'Awaiting review' : 'Nothing pending' }}</span>
                        </a>

                        <!-- Total Fines -->
                        <a class="account-stat-card account-stat-card--red" href="{{ route('student.fines') }}">
                            <span class="account-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z" />
                                    <path d="M16 8h-6" />
                                    <path d="M16 12h-6" />
                                    <path d="M13 16h-3" />
                                </svg>
                            </span>
                            <span class="account-stat-label">Total Fines</span>
                            <span class="account-stat-value">{{ \App\Support\Currency::format($totalFinesAmount) }}</span>
                            <span class="account-stat-context">Total assessed</span>
                        </a>

                        <!-- Fines Paid -->
                        <a class="account-stat-card account-stat-card--green" href="{{ route('student.fines') }}">
                            <span class="account-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <path d="M2 10h20" />
                                    <path d="m15 15 1.5 1.5L20 13" />
                                </svg>
                            </span>
                            <span class="account-stat-label">Fines Paid</span>
                            <span class="account-stat-value">{{ \App\Support\Currency::format($finesPaidAmount) }}</span>
                            <span class="account-stat-context">Successfully paid</span>
                        </a>

                        <!-- Approved Requests -->
                        <a class="account-stat-card account-stat-card--teal" href="{{ route('student.requests') }}">
                            <span class="account-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="m8 12 2.5 2.5L16 9" />
                                </svg>
                            </span>
                            <span class="account-stat-label">Approved Requests</span>
                            <span class="account-stat-value">{{ $approvedRequestsCount }}</span>
                            <span class="account-stat-context">Requests approved</span>
                        </a>
                    </div>
                </section>

                <!-- Profile & Settings -->
                <div class="p-6 profile-card student-settings-shell" id="profileSettings">
                    @include('Student.partials.profile-settings-panel', [
                        'user' => $user,
                        'student' => $student,
                        'profilePhotoUrl' => $profilePhotoUrl,
                        'departmentName' => $departmentName,
                        'addressValue' => $addressValue,
                        'accountDeletionState' => $accountDeletionState,
                    ])
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    @include('shared.account-deletion.scripts')
    @include('Student.partials.profile-settings-script')
@endpush
