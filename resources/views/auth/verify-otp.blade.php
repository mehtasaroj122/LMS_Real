@php
    $initialDigits = [
        old('otp1'),
        old('otp2'),
        old('otp3'),
        old('otp4'),
        old('otp5'),
        old('otp6'),
    ];
    $otpError = $errors->first('otp') ?: $errors->first('otp1') ?: $errors->first('email') ?: $errors->first();
@endphp

<x-layouts.auth
    title="Verify OTP"
    eyebrow="Email verification"
    heading="Verify your email address"
>
    <x-auth-card
        class="space-y-5"
        id="otp-verification-card"
        data-email="{{ $email }}"
        data-resend-url="{{ route('resend.otp') }}"
        data-csrf-token="{{ csrf_token() }}"
        data-initial-countdown="{{ $resendCooldownSeconds ?? 0 }}"
    >
        @if ($otpError)
            <x-auth-alert id="otp-server-error" variant="danger" :message="$otpError" />
        @endif

        <div
            id="otp-feedback"
            class="hidden rounded-2xl border px-4 py-3 text-sm"
            role="status"
            aria-live="polite"
        >
            <p id="otp-feedback-text"></p>
        </div>

        <form method="POST" action="{{ route('verify.otp') }}" class="space-y-6" id="otp-verification-form">
            @csrf

            <input type="hidden" name="email" value="{{ $email }}">

            <p class="text-sm text-slate-600 dark:text-slate-300">
                Code sent to
                <span class="font-medium text-slate-800 dark:text-white">{{ $email }}</span>
            </p>

            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Enter the 6-digit code</p>

                <div class="mt-4 grid grid-cols-6 gap-2 sm:gap-3">
                    @for ($index = 0; $index < 6; $index++)
                        @php
                            $inputName = 'otp' . ($index + 1);
                        @endphp
                        <input
                            id="{{ $inputName }}"
                            name="{{ $inputName }}"
                            type="text"
                            value="{{ $initialDigits[$index] ?? '' }}"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="1"
                            autocomplete="one-time-code"
                            aria-label="OTP digit {{ $index + 1 }}"
                            required
                            data-otp-input
                            data-otp-index="{{ $index }}"
                            class="h-14 w-full rounded-2xl border border-slate-200 bg-white/90 text-center text-2xl font-semibold text-slate-900 shadow-sm outline-none transition duration-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-400/10"
                        >
                    @endfor
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <x-button-primary text="Verify OTP" icon="check" id="verify-otp-button" />

                <button
                    type="button"
                    id="resend-otp-button"
                    class="inline-flex h-16 w-full items-center justify-center rounded-2xl border border-sky-200 bg-white px-5 text-center text-base font-semibold text-slate-800 shadow-sm shadow-slate-950/5 transition duration-200 hover:-translate-y-0.5 hover:border-sky-300 hover:bg-sky-50/90 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 disabled:cursor-not-allowed disabled:hover:translate-y-0 dark:border-sky-500/30 dark:bg-slate-950/60 dark:text-slate-100 dark:hover:border-sky-400 dark:hover:bg-slate-900"
                >
                    <span id="resend-otp-label">Resend OTP</span>
                </button>
            </div>
        </form>
    </x-auth-card>

    <script>
        (() => {
            const card = document.getElementById('otp-verification-card');

            if (!(card instanceof HTMLElement)) {
                return;
            }

            const form = document.getElementById('otp-verification-form');
            const verifyButton = document.getElementById('verify-otp-button');
            const resendButton = document.getElementById('resend-otp-button');
            const resendLabel = document.getElementById('resend-otp-label');
            const serverErrorAlert = document.getElementById('otp-server-error');
            const feedback = document.getElementById('otp-feedback');
            const feedbackText = document.getElementById('otp-feedback-text');
            const otpInputs = Array.from(document.querySelectorAll('[data-otp-input]'));
            const email = card.dataset.email ?? '';
            const resendUrl = card.dataset.resendUrl ?? '';
            const csrfToken = card.dataset.csrfToken ?? '';
            const initialCountdown = Number.parseInt(card.dataset.initialCountdown ?? '0', 10) || 0;

            let countdown = Math.max(0, initialCountdown);
            let timer = null;
            let resending = false;
            let verifying = false;
            const verifyButtonDefaultMarkup = verifyButton instanceof HTMLButtonElement ? verifyButton.innerHTML : '';

            const feedbackClasses = {
                success: 'rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100',
                danger: 'rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-100',
            };

            const syncFilledState = (input) => {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                const hasValue = input.value.trim() !== '';
                input.classList.toggle('border-sky-400', hasValue);
                input.classList.toggle('bg-sky-50/80', hasValue);
                input.classList.toggle('dark:border-sky-400', hasValue);
                input.classList.toggle('dark:bg-sky-500/10', hasValue);
            };

            const showFeedback = (type, message) => {
                if (!(feedback instanceof HTMLElement) || !(feedbackText instanceof HTMLElement)) {
                    return;
                }

                const resolvedType = type === 'success' ? 'success' : 'danger';
                feedback.className = feedbackClasses[resolvedType];
                feedback.hidden = false;
                feedback.classList.remove('hidden');
                feedback.setAttribute('role', resolvedType === 'success' ? 'status' : 'alert');
                feedback.setAttribute('aria-live', resolvedType === 'success' ? 'polite' : 'assertive');
                feedbackText.textContent = message;
            };

            const clearFeedback = () => {
                if (!(feedback instanceof HTMLElement) || !(feedbackText instanceof HTMLElement)) {
                    return;
                }

                feedback.hidden = true;
                feedback.classList.add('hidden');
                feedbackText.textContent = '';
            };

            const clearServerError = () => {
                if (!(serverErrorAlert instanceof HTMLElement)) {
                    return;
                }

                serverErrorAlert.hidden = true;
                serverErrorAlert.classList.add('hidden');
            };

            const focusDigit = (index) => {
                const safeIndex = Math.max(0, Math.min(index, otpInputs.length - 1));
                const input = otpInputs[safeIndex];

                if (input instanceof HTMLInputElement) {
                    input.focus();
                    input.select();
                }
            };

            const resetInputs = () => {
                otpInputs.forEach((input) => {
                    input.value = '';
                    syncFilledState(input);
                });
            };

            const isOtpComplete = () => otpInputs.every((input) => input.value.trim() !== '');

            const setVerifyingState = (state) => {
                verifying = state;

                if (verifyButton instanceof HTMLButtonElement) {
                    verifyButton.disabled = state;
                    verifyButton.setAttribute('aria-busy', state ? 'true' : 'false');
                    verifyButton.innerHTML = state
                        ? `
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                            </svg>
                            <span>Verifying OTP</span>
                        `
                        : verifyButtonDefaultMarkup;
                }

                if (resendButton instanceof HTMLButtonElement) {
                    resendButton.disabled = state || resending || countdown > 0;
                }
            };

            const submitOtpForm = () => {
                if (!(form instanceof HTMLFormElement) || verifying || !isOtpComplete()) {
                    return;
                }

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(verifyButton instanceof HTMLButtonElement ? verifyButton : undefined);
                    return;
                }

                form.submit();
            };

            const updateResendButton = () => {
                if (!(resendButton instanceof HTMLButtonElement) || !(resendLabel instanceof HTMLElement)) {
                    return;
                }

                if (verifying) {
                    resendButton.disabled = true;
                    return;
                }

                if (resending) {
                    resendButton.disabled = true;
                    resendLabel.textContent = 'Sending new OTP...';
                    resendButton.classList.add('border-sky-200', 'bg-sky-50/95', 'text-sky-800', 'dark:border-sky-500/30', 'dark:bg-sky-500/10', 'dark:text-sky-100');
                    resendButton.classList.remove('border-slate-200', 'bg-slate-100/85', 'text-slate-500', 'dark:border-slate-700', 'dark:bg-slate-900/70', 'dark:text-slate-300');
                    return;
                }

                if (countdown > 0) {
                    resendButton.disabled = true;
                    resendLabel.textContent = `Resend OTP in ${countdown}s`;
                    resendButton.classList.add('border-slate-200', 'bg-slate-100/85', 'text-slate-500', 'dark:border-slate-700', 'dark:bg-slate-900/70', 'dark:text-slate-300');
                    resendButton.classList.remove('border-sky-200', 'bg-sky-50/95', 'text-sky-800', 'dark:border-sky-500/30', 'dark:bg-sky-500/10', 'dark:text-sky-100');
                    return;
                }

                resendButton.disabled = false;
                resendLabel.textContent = 'Resend OTP';
                resendButton.classList.remove('border-slate-200', 'bg-slate-100/85', 'text-slate-500', 'dark:border-slate-700', 'dark:bg-slate-900/70', 'dark:text-slate-300');
                resendButton.classList.remove('border-sky-200', 'bg-sky-50/95', 'text-sky-800', 'dark:border-sky-500/30', 'dark:bg-sky-500/10', 'dark:text-sky-100');
            };

            const startTimer = () => {
                if (timer) {
                    window.clearInterval(timer);
                    timer = null;
                }

                updateResendButton();

                if (countdown <= 0) {
                    return;
                }

                timer = window.setInterval(() => {
                    countdown = Math.max(0, countdown - 1);
                    updateResendButton();

                    if (countdown <= 0 && timer) {
                        window.clearInterval(timer);
                        timer = null;
                    }
                }, 1000);
            };

            otpInputs.forEach((input, index) => {
                syncFilledState(input);

                input.addEventListener('focus', () => {
                    input.select();
                });

                input.addEventListener('input', (event) => {
                    const target = event.currentTarget;

                    if (!(target instanceof HTMLInputElement)) {
                        return;
                    }

                    clearFeedback();
                    clearServerError();

                    const sanitized = String(target.value ?? '').replace(/\D/g, '').slice(0, 1);
                    target.value = sanitized;
                    syncFilledState(target);

                    if (sanitized !== '' && index < otpInputs.length - 1) {
                        focusDigit(index + 1);
                    }

                    if (isOtpComplete()) {
                        window.setTimeout(() => {
                            submitOtpForm();
                        }, 120);
                    }
                });

                input.addEventListener('keydown', (event) => {
                    if (!(event.currentTarget instanceof HTMLInputElement)) {
                        return;
                    }

                    const target = event.currentTarget;

                    if (event.key === 'Backspace' && target.value === '' && index > 0) {
                        focusDigit(index - 1);
                    }
                });

                input.addEventListener('paste', (event) => {
                    event.preventDefault();

                    clearFeedback();
                    clearServerError();

                    const pastedDigits = event.clipboardData
                        ?.getData('text')
                        .replace(/\D/g, '')
                        .slice(0, otpInputs.length)
                        .split('') ?? [];

                    if (pastedDigits.length === 0) {
                        return;
                    }

                    otpInputs.forEach((field, fieldIndex) => {
                        field.value = pastedDigits[fieldIndex] ?? '';
                        syncFilledState(field);
                    });

                    const nextIndex = Math.min(pastedDigits.length, otpInputs.length - 1);
                    focusDigit(nextIndex);

                    if (isOtpComplete()) {
                        window.setTimeout(() => {
                            submitOtpForm();
                        }, 120);
                    }
                });
            });

            const firstEmptyIndex = otpInputs.findIndex((input) => input.value.trim() === '');
            focusDigit(firstEmptyIndex === -1 ? 0 : firstEmptyIndex);
            startTimer();

            if (form instanceof HTMLFormElement) {
                form.addEventListener('submit', (event) => {
                    if (verifying) {
                        event.preventDefault();
                        return;
                    }

                    clearFeedback();

                    const emptyIndex = otpInputs.findIndex((input) => input.value.trim() === '');

                    if (emptyIndex !== -1) {
                        event.preventDefault();
                        showFeedback('danger', 'Please enter the full 6-digit OTP code before continuing.');
                        focusDigit(emptyIndex);
                        return;
                    }

                    setVerifyingState(true);
                });
            }

            if (resendButton instanceof HTMLButtonElement) {
                resendButton.addEventListener('click', async () => {
                    if (resending || countdown > 0) {
                        return;
                    }

                    clearFeedback();
                    resending = true;
                    updateResendButton();

                    try {
                        const response = await fetch(resendUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ email }),
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok || !data.success) {
                            if (Number.isFinite(Number(data.wait_seconds))) {
                                countdown = Math.max(0, Number.parseInt(data.wait_seconds, 10) || 0);
                                startTimer();
                            }

                            throw new Error(data.message || 'Unable to resend OTP right now. Please try again.');
                        }

                        showFeedback('success', data.message || 'OTP has been resent to your email.');
                        resetInputs();
                        countdown = Math.max(0, Number.parseInt(data.cooldown_seconds ?? 60, 10) || 60);
                        focusDigit(0);
                        startTimer();
                    } catch (error) {
                        showFeedback('danger', error.message || 'Unable to resend OTP right now. Please try again.');
                    } finally {
                        resending = false;
                        updateResendButton();
                    }
                });
            }
        })();
    </script>
</x-layouts.auth>
