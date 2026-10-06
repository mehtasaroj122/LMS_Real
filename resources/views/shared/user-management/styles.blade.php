<style>
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 16px;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        body.light-theme .modal {
            background-color: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        body.dark-theme .modal {
            background-color: #1e293b;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px;
            border-bottom: 1px solid;
        }

        body.light-theme .modal-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .modal-header {
            border-color: #334155;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }

        .modal-close-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 20px;
            color: #64748b;
        }

        body.dark-theme .modal-close-btn {
            color: #94a3b8;
        }

        .modal-close-btn:hover {
            background-color: #f3f4f6;
        }

        body.dark-theme .modal-close-btn:hover {
            background-color: #334155;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-description {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 24px;
        }

        body.dark-theme .modal-description {
            color: #94a3b8;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid;
        }

        body.light-theme .modal-footer {
            border-color: #e5e7eb;
        }

        body.dark-theme .modal-footer {
            border-color: #334155;
        }

        /* Form Styles */
        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .required::after {
            content: "*";
            color: #ef4444;
            margin-left: 4px;
        }

        .form-control {
            box-sizing: border-box;
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        body.light-theme .form-control {
            background-color: #ffffff;
            color: #0f172a;
        }

        body.dark-theme .form-control {
            background-color: #1e293b;
            border-color: #475569;
            color: #e2e8f0;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme .form-control:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }

        body.dark-theme select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        }

        .status-radio {
            display: flex;
            gap: 20px;
            margin-top: 8px;
        }

        .status-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .status-option input[type="radio"] {
            margin: 0;
        }

        /* Conditional Field Styles */
        .conditional-field {
            transition: all 0.3s ease;
        }

        .conditional-field.hidden {
            display: none !important;
        }

        .conditional-field .form-label.required::after {
            content: ' *';
            color: #ef4444;
        }

        /* Field Error Styles */
        .field-error {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: none;
            font-weight: 500;
        }

        .field-error.visible {
            display: block;
        }

        .form-control.is-invalid {
            border-color: #ef4444 !important;
            padding-right: 35px;
        }

        .form-control.is-valid {
            border-color: #10b981 !important;
            padding-right: 35px;
        }

        .form-control.is-pending {
            border-color: #f59e0b !important;
            padding-right: 35px;
        }

        select.form-control.is-valid,
        select.form-control.is-invalid,
        select.form-control.is-pending {
            padding-right: 64px;
        }

        .status-radio.is-invalid {
            outline: 1px solid #ef4444;
            border-radius: 10px;
            padding: 8px 10px;
        }

        .status-radio.is-valid {
            outline: 1px solid #10b981;
            border-radius: 10px;
            padding: 8px 10px;
        }

        .field-validation-icon {
            position: absolute;
            right: 12px;
            top: 42px;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease, color 0.2s ease;
        }

        select.form-control ~ .field-validation-icon {
            right: 38px;
        }

        .form-group.has-valid .field-validation-icon,
        .form-group.has-invalid .field-validation-icon,
        .form-group.has-pending .field-validation-icon {
            opacity: 1;
        }

        .form-group.has-valid .field-validation-icon {
            color: #10b981;
        }

        .form-group.has-invalid .field-validation-icon {
            color: #ef4444;
        }

        .form-group.has-pending .field-validation-icon {
            color: #f59e0b;
        }

        body.dark-theme .form-group.has-valid .field-validation-icon {
            color: #34d399;
        }

        body.dark-theme .form-group.has-invalid .field-validation-icon {
            color: #f87171;
        }

        body.dark-theme .form-group.has-pending .field-validation-icon {
            color: #fbbf24;
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .form-group.error .form-control {
            border-color: #ef4444;
        }

</style>
