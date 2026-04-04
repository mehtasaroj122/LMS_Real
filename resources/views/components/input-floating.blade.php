@props([
    'name',
    'label',
    'type' => 'text',
    'id' => null,
    'value' => null,
    'hint' => null,
    'error' => null,
    'containerClass' => '',
    'required' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'readonly' => false,
    'disabled' => false,
])

@php
    $inputId = $id ?: $name;
    $errorMessage = $error ?? $errors->first($name);
    $resolvedValue = old($name, $value);
    $hasValue = $resolvedValue !== null && trim((string) $resolvedValue) !== '';
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
    x-data="{ filled: @js($hasValue) }"
    x-init="$nextTick(() => { filled = Boolean($refs.input?.value); setTimeout(() => { filled = Boolean($refs.input?.value); }, 120); })"
>
    <div
        @class(['auth-outlined-field', 'is-error' => $errorMessage])
        data-auth-field
        data-filled="{{ $hasValue ? 'true' : 'false' }}"
        x-bind:data-filled="filled ? 'true' : 'false'"
    >
        <input
            {{ $attributes->except(['class', 'aria-describedby'])->merge(['class' => 'auth-outlined-input']) }}
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ $resolvedValue }}"
            data-auth-input
            x-ref="input"
            x-on:blur="filled = Boolean($event.target.value)"
            x-on:change="filled = Boolean($event.target.value)"
            x-on:input="filled = Boolean($event.target.value)"
            @if ($required) required @endif
            @if ($autofocus) autofocus @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($readonly) readonly @endif
            @if ($disabled) disabled @endif
            aria-invalid="{{ $errorMessage ? 'true' : 'false' }}"
            @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        >

        <label for="{{ $inputId }}" class="auth-outlined-label">
            {{ $label }}
        </label>

        <span class="auth-field-validation-icon" data-auth-icon aria-hidden="true"></span>
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
