<?php

use App\Mail\BookReturnedSimple;

test('book returned email explains that recorded fines are already paid without rendering a logo block', function () {
    $html = (new BookReturnedSimple(
        'reader@example.com',
        'Reader One',
        'HTTP-2 in Action',
        'good',
        500
    ))->render();

    expect($html)->toContain('Fine settled:');
    expect($html)->toContain('A fine was recorded during return processing and has already been marked as paid.');
    expect($html)->not->toContain('Payment needed:');
    expect($html)->not->toContain('class="brand-logo"');
    expect($html)->not->toContain('class="brand-badge"');
});
