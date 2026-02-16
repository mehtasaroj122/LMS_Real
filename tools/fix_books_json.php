<?php
$input = __DIR__ . '/../database/JSON/books.json';
$output = $input;
if (!file_exists($input)) {
    echo "books.json not found: $input\n";
    exit(1);
}
$json = file_get_contents($input);
$data = json_decode($json, true);
if ($data === null) {
    echo "Failed to decode JSON\n";
    exit(1);
}
$changed = 0;
foreach ($data as &$item) {
    if (array_key_exists('cover_image', $item) && $item['cover_image'] !== null) {
        $item['cover_image'] = null;
        $changed++;
    }
}
file_put_contents($output, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Updated $changed entries in books.json\n";
