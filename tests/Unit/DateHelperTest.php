<?php

use App\Helpers\DateHelper;
use Tests\TestCase;

uses(TestCase::class);

test('it formats Bikram Sambat dates in English dashboard order', function () {
    expect(DateHelper::formatAsBikramSambat('2026-10-07'))
        ->toBe('Wednesday, 21 Ashoj 2083');
});
