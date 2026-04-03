<?php

use App\Mail\StudentPrivilegeUpdatedMail;
use App\Mail\StudentStatusUpdatedMail;

test('student status email renders a clear deactivation warning', function () {
    $html = (new StudentStatusUpdatedMail(
        'reader@example.com',
        'Reader One',
        'inactive',
        'Jane Admin',
        'Administrator',
        '2026-04-04 10:30',
    ))->render();

    expect($html)->toContain('Your account has been deactivated');
    expect($html)->toContain('Jane Admin (Administrator)');
    expect($html)->toContain('Borrowing, renewals, and book requests may be restricted');
});

test('student privilege update email renders the current library settings', function () {
    $html = (new StudentPrivilegeUpdatedMail(
        'reader@example.com',
        'Reader One',
        [
            ['label' => 'Maximum books', 'value' => '7'],
            ['label' => 'Issue duration', 'value' => '21 days'],
            ['label' => 'Per-day fine', 'value' => 'Rs. 15.00'],
            ['label' => 'Grace period', 'value' => '2 days'],
            ['label' => 'Maximum fine', 'value' => 'Rs. 500.00'],
            ['label' => 'Borrowing access', 'value' => 'Restricted'],
        ],
        'Jane Admin',
        'Administrator',
        '2026-04-04 10:30',
        false,
        'Max Books: 5 -> 7',
    ))->render();

    expect($html)->toContain('Your library privileges were updated');
    expect($html)->toContain('Borrowing access');
    expect($html)->toContain('Restricted');
    expect($html)->toContain('Important:');
});

test('staff status email renders staff-specific access guidance', function () {
    $html = (new StudentStatusUpdatedMail(
        'staff@example.com',
        'Staff User',
        'inactive',
        'Jane Admin',
        'Administrator',
        '2026-04-04 10:30',
        'staff',
    ))->render();

    expect($html)->toContain('staff dashboard, circulation tools');
    expect($html)->not->toContain('Borrowing, renewals, and book requests may be restricted');
});
