<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class AuditDetails
{
    /** Resolve only the current page's resources in batches, never within the row loop. */
    public static function forLogs(Collection $logs): Collection
    {
        $references = $logs->mapWithKeys(fn (ActivityLog $log) => [$log->id => self::reference($log)]);
        $models = ['fine' => Fine::class, 'issued_book' => IssuedBook::class, 'book' => Book::class,
            'student' => Student::class, 'user' => User::class, 'book_request' => BookRequest::class];
        $resources = [];
        foreach ($models as $type => $model) {
            $ids = $references->where('type', $type)->pluck('id')->filter()->unique();
            $query = $model::query()->whereIn('id', $ids);
            if ($type === 'fine') {
                $query->with('issuedBook.bookCopy');
            }
            if ($type === 'issued_book') {
                $query->with('bookCopy');
            }
            $resources[$type] = $ids->isEmpty() ? collect() : $query->get()->keyBy('id');
        }

        return $logs->mapWithKeys(function (ActivityLog $log) use ($references, $resources) {
            $reference = $references[$log->id];
            $resource = ($resources[$reference['type']] ?? collect())->get($reference['id']);
            $url = $resource ? match ($reference['type']) {
                'fine' => route('admin.fines.index', ['student' => $resource->student_id]),
                'issued_book' => route('admin.transactions.index', ['student' => $resource->student_id]),
                'book' => route('admin.books.show', $resource->id),
                'student' => route('admin.students.show', $resource->id),
                'user' => route('admin.users.index', ['search' => $resource->email]),
                'book_request' => route('admin.book-requests.index'),
                default => null,
            } : null;
            $style = AuditEventStyle::resolve($log->action, $log->action_category, $log->status, $log->metadata ?? []);

            return [$log->id => [
                'id' => $log->id, 'type' => str_replace('_', '-', (string) $log->action),
                'title' => Str::headline($log->action ?: 'Activity'),
                'description' => $log->readable_description,
                'fullTimestamp' => $log->created_at?->format('M d, Y h:i:s A'),
                'status' => ['danger' => 'error', 'routine' => 'info'][$style['severity']] ?? $style['severity'],
                'eventStyle' => $style,
                'userName' => $log->user_name ?: $log->user?->name ?: 'System',
                'userRole' => $log->user_role ?: 'system',
                'roleClass' => AuditEventStyle::roleClass($log->user_role),
                'resourceType' => Str::headline($reference['type']), 'resourceId' => $reference['id'],
                'resourceAvailable' => $url !== null, 'resourceUrl' => $url,
                'accessionNumber' => match ($reference['type']) {
                    'fine' => $resource?->issuedBook?->bookCopy?->accession_number,
                    'issued_book' => $resource?->bookCopy?->accession_number,
                    default => null,
                },
                'metadata' => $log->metadata ?? [], 'browser' => $log->browser,
                'deviceType' => $log->device_type, 'ipAddress' => $log->ip_address,
                'sessionId' => data_get($log->metadata, 'session_id') ?? data_get($log->metadata, 'session.id'),
            ]];
        });
    }

    private static function reference(ActivityLog $log): array
    {
        // Specific resource identifiers take priority over legacy Student model references.
        foreach (['fine_id' => 'fine', 'issued_book_id' => 'issued_book', 'book_request_id' => 'book_request'] as $key => $type) {
            if ($id = data_get($log->metadata, $key)) {
                return ['type' => $type, 'id' => $id];
            }
        }
        $type = $log->resource_type ?: Str::snake(class_basename($log->model_type ?: 'resource'));

        return ['type' => strtolower($type), 'id' => $log->resource_id ?: $log->model_id];
    }
}
