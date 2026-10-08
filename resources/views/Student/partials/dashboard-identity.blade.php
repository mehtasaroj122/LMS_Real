@php
    $studentPhotoUrl = \App\Support\ProfilePhoto::resolveUrl($user->profile_photo);
    $studentInitials = collect(preg_split('/\s+/u', trim($user->name), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $studentSemesterLabel = trim((string) $student?->semester);
    if (filled($studentSemesterLabel) && !preg_match('/^semester\b/iu', $studentSemesterLabel)) {
        $studentSemesterLabel = 'Semester ' . $studentSemesterLabel;
    }
@endphp

<div class="dashboard-identity-section">
    <div class="dashboard-heading">
        <div>
            <p class="dashboard-eyebrow">Student Portal / Overview</p>
            <h1>Dashboard</h1>
            <p class="dashboard-greeting"><span data-dashboard-greeting>Welcome</span>. Here is your library account at a glance.</p>
        </div>
        <x-dashboard-date-time-panel />
    </div>

    <div class="identity-snapshot-grid">
        <section class="student-id-card" aria-labelledby="student-id-heading">
            <div class="id-card-header">
                <div class="id-card-brand">
                    <x-logo size="sm" :lazy="false" />
                    <div>
                        <p class="id-card-brand-name">{{ $libraryBranding['name'] ?? config('app.name') }}</p>
                        <p class="id-card-brand-caption">Library Services</p>
                    </div>
                </div>
                <h2 id="student-id-heading" class="id-card-label">Student Library ID</h2>
            </div>
            <div class="id-card-person">
                <div class="id-card-photo">
                    <span class="id-card-initials" aria-hidden="true">{{ $studentInitials }}</span>
                    @if ($studentPhotoUrl)
                        <img src="{{ $studentPhotoUrl }}" alt="{{ $user->name }}" decoding="async" data-student-id-photo>
                    @endif
                </div>
                <div class="id-card-person-info">
                    <p class="id-card-name">{{ $user->name }}</p>
                    <p class="id-card-number"><span class="sr-only">Student ID: </span>{{ $student?->display_student_id ?: 'Not provided' }}</p>
                    @if (filled($department?->name))
                        <p class="id-card-department">{{ $department->name }}</p>
                    @endif
                    <div class="id-card-academic">
                        @if (filled($student?->semester))
                            <span>{{ $studentSemesterLabel }}</span>
                        @endif
                        @if (filled($student?->batch))
                            <span>Batch {{ $student->batch }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <dl class="id-card-details">
                <div class="id-card-email">
                    <dt>Email</dt>
                    <dd>{{ $user->email ?: 'Not provided' }}</dd>
                </div>
                <div>
                    <dt>Account Status</dt>
                    <dd>
                        <span class="id-card-account {{ $user->status === 'active' ? 'is-active' : (filled($user->status) ? 'is-restricted' : '') }}">
                            <span class="status-dot" aria-hidden="true"></span>
                            {{ filled($user->status) ? \Illuminate\Support\Str::headline($user->status) : 'Not provided' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>Member Since</dt>
                    <dd>{{ $user->created_at?->copy()->timezone('Asia/Kathmandu')->format('M d, Y') ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt>Borrowing Permission</dt>
                    <dd>{{ $privilegeSettings['borrowing_allowed'] ? 'Allowed' : 'Restricted' }}</dd>
                </div>
            </dl>
            <div class="id-card-footer">
                <span class="id-card-role"><i data-lucide="graduation-cap" aria-hidden="true"></i> Student</span>
                <span>Digital Library Identity</span>
            </div>
        </section>

        <section class="snapshot-section" aria-labelledby="snapshot-heading">
            <div class="snapshot-heading">
                <h2 id="snapshot-heading">Account Snapshot</h2>
                <span>Your library record</span>
            </div>
            <div class="snapshot-grid">
                <a href="{{ route('student.my-books') }}" class="snapshot-card">
                    <div class="snapshot-card-top"><span class="icon-tile"><i data-lucide="book-open" aria-hidden="true"></i></span><i data-lucide="arrow-up-right" class="snapshot-arrow" aria-hidden="true"></i></div>
                    <span class="snapshot-value">{{ number_format($booksIssuedCount) }}</span>
                    <span class="snapshot-label">Currently Issued</span>
                    <span class="snapshot-caption">View your borrowed books</span>
                </a>
                <div class="snapshot-card">
                    <div class="snapshot-card-top"><span class="icon-tile returned"><i data-lucide="book-check" aria-hidden="true"></i></span></div>
                    <span class="snapshot-value">{{ number_format($booksReturnedCount) }}</span>
                    <span class="snapshot-label">Books Returned</span>
                    <span class="snapshot-caption">All-time returns</span>
                </div>
                <a href="{{ route('student.fines') }}" class="snapshot-card">
                    <div class="snapshot-card-top"><span class="icon-tile fines"><i data-lucide="coins" aria-hidden="true"></i></span><i data-lucide="arrow-up-right" class="snapshot-arrow" aria-hidden="true"></i></div>
                    <span class="snapshot-value currency">{{ \App\Support\Currency::format($pendingFines, 2) }}</span>
                    <span class="snapshot-label">Pending Fines</span>
                    <span class="snapshot-caption">View your fine details</span>
                </a>
                <a href="{{ route('student.requests') }}" class="snapshot-card">
                    <div class="snapshot-card-top"><span class="icon-tile requests"><i data-lucide="clipboard-list" aria-hidden="true"></i></span><i data-lucide="arrow-up-right" class="snapshot-arrow" aria-hidden="true"></i></div>
                    <span class="snapshot-value">{{ number_format($activeRequestsCount) }}</span>
                    <span class="snapshot-label">Active Requests</span>
                    <span class="snapshot-caption">Awaiting approval</span>
                </a>
            </div>
        </section>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const studentPhoto = document.querySelector('[data-student-id-photo]');
            if (studentPhoto) {
                studentPhoto.addEventListener('error', () => { studentPhoto.hidden = true; });
                if (studentPhoto.complete && studentPhoto.naturalWidth === 0) {
                    studentPhoto.hidden = true;
                }
            }
        });
    </script>
@endpush
