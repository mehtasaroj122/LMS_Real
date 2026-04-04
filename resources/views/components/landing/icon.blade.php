@props(['name'])

@php
    $normalized = strtolower(trim((string) $name));
    $baseAttributes = $attributes->class('shrink-0');
@endphp

@switch($normalized)
    @case('books')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v14.5A1.5 1.5 0 0 1 18.5 20H6.5A2.5 2.5 0 0 0 4 22Z" /><path d="M8 8h8" /><path d="M8 12h8" /><path d="M8 16h5" /><path d="M4 6.5V20" /></svg>
        @break
    @case('students')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" /><circle cx="9.5" cy="7" r="3" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 4.13a4 4 0 0 1 0 7.75" /></svg>
        @break
    @case('requests')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6" /><path d="M10 12l2 2 4-4" /><path d="M9 21h6" /><path d="M8 3a2 2 0 0 0-2 2v14l3-2 3 2 3-2 3 2V5a2 2 0 0 0-2-2Z" /></svg>
        @break
    @case('workflow')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 7h11l-3-3" /><path d="M17 17H6l3 3" /><path d="M18 7v4" /><path d="M6 13v4" /></svg>
        @break
    @case('fines')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12l3 4-3 4H6L3 7Z" /><path d="M8 14h8" /><path d="M8 18h5" /><path d="M11 7h3" /></svg>
        @break
    @case('notifications')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V10a6 6 0 1 0-12 0v4.2a2 2 0 0 1-.6 1.4L4 17h5" /><path d="M10 17a2 2 0 0 0 4 0" /></svg>
        @break
    @case('admin')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-2.9 8.4-7 9.8-4.1-1.4-7-5.3-7-9.8V6Z" /><path d="M9.5 12l1.7 1.7L14.8 10" /></svg>
        @break
    @case('staff')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><path d="M3 12h18" /></svg>
        @break
    @case('student')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-5 9 5-9 5Z" /><path d="M7 11.5v4.1c0 .4.2.8.6 1.1 1.2 1 2.8 1.6 4.4 1.6s3.2-.6 4.4-1.6c.4-.3.6-.7.6-1.1v-4.1" /><path d="M21 9v5" /></svg>
        @break
    @case('login')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5" /><path d="M15 12H3" /><path d="M21 19V5a2 2 0 0 0-2-2h-5" /></svg>
        @break
    @case('search')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
        @break
    @case('approve')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="m8.5 12 2.3 2.3 4.7-4.8" /></svg>
        @break
    @case('issue')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 19V7a2 2 0 0 1 2-2h10v12a2 2 0 0 1-2 2Z" /><path d="M6 19a2 2 0 0 0-2-2V5a2 2 0 0 1 2 2" /><path d="M12 12h6" /><path d="m15 9 3 3-3 3" /></svg>
        @break
    @case('return')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 17V5a2 2 0 0 0-2-2H6v12a2 2 0 0 0 2 2Z" /><path d="M18 17a2 2 0 0 1 2 2H8" /><path d="M12 10H6" /><path d="m9 7-3 3 3 3" /></svg>
        @break
    @case('fine-notification')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V10a6 6 0 1 0-12 0v4.2a2 2 0 0 1-.6 1.4L4 17h5" /><path d="M12 9v4" /><path d="M12 16h.01" /></svg>
        @break
    @case('security')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-2.9 8.4-7 9.8-4.1-1.4-7-5.3-7-9.8V6Z" /><rect x="9" y="10" width="6" height="5" rx="1.2" /><path d="M10 10V8.8a2 2 0 0 1 4 0V10" /></svg>
        @break
    @case('lock')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V8a4 4 0 1 1 8 0v3" /></svg>
        @break
    @case('portal')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2" /><path d="M3 10h18" /><path d="M8 4v16" /></svg>
        @break
    @case('audit')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6" /><path d="M10 12h6" /><path d="M10 16h4" /><path d="M8 3a2 2 0 0 0-2 2v14l3-2 3 2 3-2 3 2V5a2 2 0 0 0-2-2Z" /></svg>
        @break
    @case('automation')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m13 2-2 7h4l-4 13" /><path d="M5 12h3" /><path d="M16 5h3" /><path d="M18 16h3" /></svg>
        @break
    @case('college')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18" /><path d="M6 21V9l6-4 6 4v12" /><path d="M10 13h4" /><path d="M10 17h4" /></svg>
        @break
    @case('school')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18" /><path d="M4 21V10l8-5 8 5v11" /><path d="M9 21v-5h6v5" /><path d="M12 5v5" /></svg>
        @break
    @case('department')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21V7a2 2 0 0 1 2-2h8v16" /><path d="M14 21V11h4a2 2 0 0 1 2 2v8" /><path d="M8 9h2" /><path d="M8 13h2" /><path d="M8 17h2" /></svg>
        @break
    @case('menu')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16" /><path d="M4 12h16" /><path d="M4 17h16" /></svg>
        @break
    @case('close')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 6-12 12" /><path d="m6 6 12 12" /></svg>
        @break
    @case('moon')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z" /></svg>
        @break
    @case('sun')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4" /><path d="M12 2v2.5" /><path d="M12 19.5V22" /><path d="M4.9 4.9 6.7 6.7" /><path d="m17.3 17.3 1.8 1.8" /><path d="M2 12h2.5" /><path d="M19.5 12H22" /><path d="m4.9 19.1 1.8-1.8" /><path d="m17.3 6.7 1.8-1.8" /></svg>
        @break
    @case('arrow-right')
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14" /><path d="m13 5 7 7-7 7" /></svg>
        @break
    @default
        <svg {{ $baseAttributes }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /></svg>
@endswitch
