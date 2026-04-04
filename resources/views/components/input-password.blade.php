@props([
    'name',
    'label',
    'id' => null,
    'hint' => null,
    'error' => null,
    'containerClass' => '',
    'required' => false,
    'autofocus' => false,
    'autocomplete' => 'current-password',
])

@php
    $inputId = $id ?: $name;
    $errorMessage = $error ?? $errors->first($name);
    $hintId = $inputId . '-hint';
    $errorId = $inputId . '-error';
    $describedByTokens = [];
    $providedDescribedBy = $attributes->get('aria-describedby');

    if ($providedDescribedBy) {
        $describedByTokens[] = $providedDescribedBy;
    }

    if ($hint) {
        $describedByTokens[] = $hintId;
    }

    if ($errorMessage) {
        $describedByTokens[] = $errorId;
    }

    $describedBy = trim(implode(' ', array_filter($describedByTokens)));
@endphp

<div
    class="{{ trim('auth-field-group space-y-1.5 ' . $containerClass) }}"
    data-auth-field-group="{{ $name }}"
    x-data="{ revealed: false, filled: false }"
    x-init="$nextTick(() => { filled = Boolean($refs.input?.value); setTimeout(() => { filled = Boolean($refs.input?.value); }, 120); })"
>
    <div
        @class(['auth-outlined-field has-trailing-action', 'is-error' => $errorMessage])
        data-auth-field
        data-filled="false"
        x-bind:data-filled="filled ? 'true' : 'false'"
    >
        <input
            {{ $attributes->except(['class', 'aria-describedby'])->merge(['class' => 'auth-outlined-input']) }}
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="password"
            x-bind:type="revealed ? 'text' : 'password'"
            data-auth-input
            x-ref="input"
            x-on:blur="filled = Boolean($event.target.value)"
            x-on:change="filled = Boolean($event.target.value)"
            x-on:input="filled = Boolean($event.target.value)"
            @if ($required) required @endif
            @if ($autofocus) autofocus @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            aria-invalid="{{ $errorMessage ? 'true' : 'false' }}"
            @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        >

        <label for="{{ $inputId }}" class="auth-outlined-label">
            {{ $label }}
        </label>

        <span class="auth-field-validation-icon" data-auth-icon aria-hidden="true"></span>

        <button
            type="button"
            x-cloak
            class="absolute right-2.5 top-1/2 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full text-slate-500 transition duration-200 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
            x-on:click="revealed = !revealed"
            x-bind:aria-label="revealed ? 'Hide password' : 'Show password'"
            x-bind:aria-pressed="revealed.toString()"
            aria-controls="{{ $inputId }}"
        >
            <svg x-cloak x-show="!revealed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg x-cloak x-show="revealed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m3 3 18 18"/>
                <path d="M10.584 10.587a2 2 0 1 0 2.828 2.826"/>
                <path d="M9.363 5.365A10.62 10.62 0 0 1 12 5c4.756 0 8.572 3.118 9.777 7a10.474 10.474 0 0 1-2.109 3.592"/>
                <path d="M6.228 6.233A10.45 10.45 0 0 0 2.25 12c1.205 3.882 5.021 7 9.75 7a10.6 10.6 0 0 0 5.182-1.319"/>
            </svg>
            <span class="sr-only" x-text="revealed ? 'Hide password' : 'Show password'"></span>
        </button>
    </div>

    @if ($hint)
        <p id="{{ $hintId }}" class="auth-field-hint text-sm text-slate-500 dark:text-slate-400" data-auth-hint>
            {{ $hint }}
        </p>
    @endif

    <p
        id="{{ $errorId }}"
        class="auth-field-error {{ $errorMessage ? 'is-visible' : '' }}"
        data-auth-error
        @if (! $errorMessage) hidden @endif
    >
        {{ $errorMessage }}
    </p>
</div>
