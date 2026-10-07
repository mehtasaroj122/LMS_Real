<?php

use App\Services\Code128Barcode;

test('code 128 renderer creates a vector barcode with quiet zones and accessible text', function () {
    $svg = app(Code128Barcode::class)->svg('A');

    expect($svg)
        ->toContain('viewBox="0 0 66 58"')
        ->toContain('Code 128 barcode for A')
        ->toContain('shape-rendering="crispEdges"')
        ->toContain('fill="#000"');
});

test('code 128 renderer rejects non printable values', function () {
    expect(fn () => app(Code128Barcode::class)->svg("ACC-000001\n"))
        ->toThrow(InvalidArgumentException::class);
});
