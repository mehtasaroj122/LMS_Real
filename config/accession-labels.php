<?php

return [
    'max_per_batch' => (int) env('ACCESSION_LABEL_MAX_BATCH', 500),
    'max_scan' => (int) env('ACCESSION_LABEL_MAX_SCAN', 2000),
    'max_total_print_labels' => (int) env('ACCESSION_LABEL_MAX_PRINT', 2500),
    'preview_page_size' => 50,
    'default_label_size' => 'medium',
    'default_columns' => 3,
    'default_page_size' => 'A4',
    'default_orientation' => 'portrait',
    'default_barcode_height' => 64,
    'label_sizes' => [
        'small' => ['label' => 'Small', 'width_mm' => 35, 'height_mm' => 22, 'max_columns' => 5],
        'medium' => ['label' => 'Medium', 'width_mm' => 62, 'height_mm' => 32, 'max_columns' => 3],
        'large' => ['label' => 'Large', 'width_mm' => 90, 'height_mm' => 45, 'max_columns' => 2],
    ],
    'page_sizes' => ['A4', 'Letter'],
];
