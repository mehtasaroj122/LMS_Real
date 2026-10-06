<?php

namespace App\Support;

use App\Models\ActivityLog;

final class AuditDescription
{
    /** Plain text segments let Blade escape both recorded descriptions and resource names. */
    public static function segments(ActivityLog $log): array
    {
        $entities = collect(['book_title', 'book_name', 'student_label', 'student_name'])
            ->map(fn ($key) => data_get($log->metadata, $key))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->unique()->sortByDesc(fn ($value) => mb_strlen($value))
            ->map(fn ($value) => preg_quote($value, '/'))->implode('|');
        $pattern = '/('.($entities !== '' ? $entities.'|' : '').'रु\s*[\d,.]+|[“\"][^”\"]+[”\"])/u';
        $parts = preg_split($pattern, $log->readable_description, -1, PREG_SPLIT_DELIM_CAPTURE);

        return collect($parts ?: [$log->readable_description])
            ->map(fn ($text, $index) => ['text' => $text, 'emphasis' => $index % 2 === 1])->all();
    }
}
