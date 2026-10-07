<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateAccessionLabelsRequest;
use App\Models\Book;
use App\Models\BookCopy;
use App\Services\AccessionNumberGenerator;
use App\Services\Code128Barcode;
use App\Support\LibraryBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccessionLabelController extends Controller
{
    public function index(AccessionNumberGenerator $generator)
    {
        Gate::authorize('access-admin');

        return view('Admin.AccessionLabels.index', [
            'nextAvailable' => $generator->nextAvailable(),
            'maximum' => (int) config('accession-labels.max_per_batch', 500),
            'maximumScan' => (int) config('accession-labels.max_scan', 2000),
            'previewPageSize' => (int) config('accession-labels.preview_page_size', 50),
            'labelSizes' => config('accession-labels.label_sizes'),
            'defaults' => [
                'label_size' => config('accession-labels.default_label_size', 'medium'),
                'columns' => (int) config('accession-labels.default_columns', 3),
                'page_size' => config('accession-labels.default_page_size', 'A4'),
                'orientation' => config('accession-labels.default_orientation', 'portrait'),
                'barcode_height' => (int) config('accession-labels.default_barcode_height', 64),
            ],
            'branding' => LibraryBranding::resolve(),
            'filterOptions' => [
                'statuses' => BookCopy::query()->whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
                'types' => BookCopy::query()->whereNotNull('book_type')->distinct()->orderBy('book_type')->pluck('book_type'),
                'conditions' => BookCopy::query()->whereNotNull('condition')->distinct()->orderBy('condition')->pluck('condition'),
            ],
        ]);
    }

    public function next(AccessionNumberGenerator $generator): JsonResponse
    {
        Gate::authorize('access-admin');

        return response()->json([
            'success' => true,
            'next_accession' => $generator->nextAvailable(),
        ]);
    }

    public function preview(
        GenerateAccessionLabelsRequest $request,
        AccessionNumberGenerator $generator,
        Code128Barcode $barcode
    ): JsonResponse {
        Gate::authorize('access-admin');

        $validated = $request->validated();
        $result = $validated['method'] === 'quantity'
            ? $generator->availableFrom(
                $validated['start'],
                (int) $validated['quantity'],
                (bool) ($validated['skip_existing'] ?? false),
                (int) config('accession-labels.max_scan', 2000)
            )
            : $generator->analyzeRange($validated['from'], $validated['to']);

        $labels = $this->barcodeLabels(collect($result['labels']), $barcode);
        $actor = $request->user();

        ActivityLogger::logActivity(
            'accession_barcodes_generated',
            sprintf(
                '%s generated %d accession barcode label%s starting from %s.',
                $actor->name,
                count($result['labels']),
                count($result['labels']) === 1 ? '' : 's',
                $result['start']
            ),
            'book',
            'accession_labels',
            null,
            [
                'from_accession' => $result['start'],
                'to_accession' => $result['last_scanned'],
                'requested_count' => $result['requested_quantity'],
                'generated_count' => count($result['labels']),
                'skipped_count' => count($result['skipped']),
                'scanned_count' => $result['scanned_count'],
                'generation_method' => $validated['method'],
                'skip_existing' => $result['skip_existing'],
                'action_type' => 'generate_preview',
                'admin_id' => $actor->id,
            ]
        );

        return response()->json([
            'success' => true,
            'requested_quantity' => $result['requested_quantity'],
            'generated_count' => count($result['labels']),
            'skipped_count' => count($result['skipped']),
            'scanned_count' => $result['scanned_count'],
            'start' => $result['start'],
            'last_scanned' => $result['last_scanned'],
            'fulfilled' => $result['fulfilled'],
            'skip_existing' => $result['skip_existing'],
            'labels' => $labels,
            'skipped' => $result['skipped'],
        ]);
    }

    public function searchBooks(Request $request): JsonResponse
    {
        Gate::authorize('access-admin');

        $validator = Validator::make($request->query(), [
            'q' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:30'],
            'book_type' => ['nullable', 'string', 'max:30'],
            'condition' => ['nullable', 'string', 'max:30'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'accession_asc', 'accession_desc', 'book_title'])],
            'per_page' => ['nullable', Rule::in([10, 25, 50, 100, '10', '25', '50', '100'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $filters = $validator->validated();
        $term = trim((string) ($filters['q'] ?? ''));
        if ($term === '') {
            return response()->json(['success' => true, 'data' => [], 'meta' => null]);
        }

        $like = '%'.addcslashes($term, '%_\\').'%';
        $query = Book::query()
            ->select(['id', 'title', 'author', 'isbn'])
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn ($copies) => $copies->where('status', 'available'),
            ])
            ->where(function ($books) use ($like): void {
                $books->where('title', 'like', $like)
                    ->orWhere('isbn', 'like', $like)
                    ->orWhereHas('copies', fn ($copies) => $copies->where('accession_number', 'like', $like));
            });

        if ($this->hasCopyFilters($filters)) {
            $query->whereHas('copies', fn ($copies) => $this->applyCopyFilters($copies, $filters));
        }

        match ($filters['sort'] ?? 'accession_asc') {
            'newest' => $query->orderByDesc('id'),
            'oldest' => $query->orderBy('id'),
            'accession_desc' => $query->orderByDesc(
                BookCopy::query()->selectRaw('MAX(accession_number)')->whereColumn('book_copies.book_id', 'books.id')
            ),
            'accession_asc' => $query->orderBy(
                BookCopy::query()->selectRaw('MIN(accession_number)')->whereColumn('book_copies.book_id', 'books.id')
            ),
            default => $query->orderBy('title'),
        };

        $paginator = $query->paginate((int) ($filters['per_page'] ?? 10));
        $books = collect($paginator->items());
        $matchedCopies = BookCopy::query()
            ->whereIn('book_id', $books->pluck('id'))
            ->where('accession_number', 'like', $like)
            ->orderBy('accession_number')
            ->get(['book_id', 'accession_number'])
            ->groupBy('book_id');

        return response()->json([
            'success' => true,
            'data' => $books->map(fn (Book $book): array => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
                'copies_count' => $book->copies_count,
                'available_copies_count' => $book->available_copies_count,
                'matched_accession' => $matchedCopies->get($book->id)?->first()?->accession_number,
            ])->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function bookCopies(Request $request, Book $book): JsonResponse
    {
        Gate::authorize('access-admin');

        $validator = Validator::make($request->query(), [
            'status' => ['nullable', 'string', 'max:30'],
            'book_type' => ['nullable', 'string', 'max:30'],
            'condition' => ['nullable', 'string', 'max:30'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'accession_asc', 'accession_desc', 'book_title'])],
            'per_page' => ['nullable', Rule::in([10, 25, 50, 100, '10', '25', '50', '100'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'highlight' => ['nullable', 'regex:/^ACC-\d{6}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $filters = $validator->validated();
        $query = $book->copies()->with('book:id,title,isbn');
        $this->applyCopyFilters($query, $filters);

        if (! empty($filters['highlight'])) {
            $query->orderByRaw('CASE WHEN accession_number = ? THEN 0 ELSE 1 END', [$filters['highlight']]);
        }

        match ($filters['sort'] ?? 'accession_asc') {
            'newest' => $query->orderByDesc('id'),
            'oldest' => $query->orderBy('id'),
            'accession_desc' => $query->orderByDesc('accession_number'),
            default => $query->orderBy('accession_number'),
        };

        $paginator = $query->paginate((int) ($filters['per_page'] ?? 10));

        return response()->json([
            'success' => true,
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
                'copies_count' => $book->copies()->count(),
            ],
            'data' => collect($paginator->items())->map(fn (BookCopy $copy): array => $this->copyPayload($copy))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function previewExisting(Request $request, Code128Barcode $barcode): JsonResponse
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'copy_ids' => ['required', 'array', 'min:1', 'max:500'],
            'copy_ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $ids = array_values(array_unique(array_map('intval', $validated['copy_ids'])));
        $copies = BookCopy::query()->with('book:id,title,isbn')->whereIn('id', $ids)->get()->keyBy('id');
        $ordered = collect($ids)->map(fn (int $id) => $copies->get($id))->filter();

        return response()->json([
            'success' => true,
            'labels' => $ordered->map(fn (BookCopy $copy): array => [
                ...$this->copyPayload($copy),
                'svg' => $barcode->svg($copy->accession_number),
            ])->values(),
            'missing_ids' => array_values(array_diff($ids, $copies->keys()->all())),
        ]);
    }

    public function preparePrint(Request $request, AccessionNumberGenerator $generator, Code128Barcode $barcode): JsonResponse
    {
        Gate::authorize('access-admin');

        $validator = Validator::make($request->all(), $this->printRules());
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $validated = $validator->validated();
        $source = $validated['source'];
        $skippedNow = [];
        $copies = collect();

        if ($source === 'generated') {
            $generationValidator = Validator::make($validated, (new GenerateAccessionLabelsRequest)->rules());
            if ($generationValidator->fails()) {
                return response()->json(['success' => false, 'message' => $generationValidator->errors()->first()], 422);
            }

            try {
                $result = $validated['method'] === 'quantity'
                    ? $generator->availableFrom(
                        $validated['start'],
                        (int) $validated['quantity'],
                        (bool) ($validated['skip_existing'] ?? false),
                        (int) config('accession-labels.max_scan', 2000)
                    )
                    : $generator->analyzeRange($validated['from'], $validated['to']);
            } catch (\InvalidArgumentException $exception) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
            }

            $available = $result['labels'];
            $requestedSelection = array_values(array_unique(array_map('strtoupper', $validated['accessions'] ?? [])));
            $accessions = $validated['print_scope'] === 'all'
                ? $available
                : array_values(array_intersect($available, $requestedSelection));
            $skippedNow = array_values(array_diff($requestedSelection, $available));
        } else {
            $ids = array_values(array_unique(array_map('intval', $validated['copy_ids'] ?? [])));
            $found = BookCopy::query()->with('book:id,title,isbn')->whereIn('id', $ids)->get()->keyBy('id');
            $copies = collect($ids)->map(fn (int $id) => $found->get($id))->filter()->values();
            $accessions = $copies->pluck('accession_number')->all();
            $skippedNow = array_values(array_diff($ids, $found->keys()->all()));
        }

        if ($accessions === []) {
            return response()->json([
                'success' => false,
                'message' => $source === 'generated'
                    ? 'No printable accession numbers remain. Generate the preview again to refresh availability.'
                    : 'The selected physical copies no longer exist.',
            ], 422);
        }

        $settings = $this->printSettings($validated);
        $copiesPerLabel = (int) $validated['copies_per_label'];
        $copyLookup = $copies->keyBy('accession_number');
        $uniqueLabels = collect($accessions)->map(fn (string $accession): array => [
            'accession' => $accession,
            'svg' => $barcode->svg($accession, $settings['barcode_height']),
            'book_title' => $copyLookup->get($accession)?->book?->title,
        ]);
        $labels = $uniqueLabels->flatMap(fn (array $label) => array_fill(0, $copiesPerLabel, $label))->values();

        if ($labels->count() > (int) config('accession-labels.max_total_print_labels', 2500)) {
            return response()->json(['success' => false, 'message' => 'This print job exceeds the maximum total label count.'], 422);
        }

        $actor = $request->user();
        $isBulkReprint = $source === 'reprint' && count($accessions) > 1;
        $action = $source === 'generated'
            ? 'accession_barcodes_printed'
            : ($isBulkReprint ? 'bulk_accession_barcodes_reprinted' : 'accession_barcode_reprinted');
        $description = $source === 'generated'
            ? sprintf('%s prepared %d generated accession labels for printing.', $actor->name, $labels->count())
            : sprintf('%s prepared %d existing accession label%s for reprinting.', $actor->name, $labels->count(), $labels->count() === 1 ? '' : 's');

        ActivityLogger::logActivity($action, $description, 'book', 'accession_labels', null, [
            'from_accession' => $accessions[0] ?? null,
            'to_accession' => $accessions[count($accessions) - 1] ?? null,
            'unique_count' => count($accessions),
            'copies_per_label' => $copiesPerLabel,
            'printed_count' => $labels->count(),
            'skipped_count' => count($skippedNow),
            'action_type' => $source === 'generated' ? 'print' : 'reprint',
            'admin_id' => $actor->id,
        ]);

        $html = view('Admin.AccessionLabels.print', [
            'labels' => $labels,
            'settings' => $settings,
            'branding' => LibraryBranding::resolve(),
            'labelPreset' => config("accession-labels.label_sizes.{$settings['label_size']}"),
            'summary' => [
                'unique_count' => count($accessions),
                'copies_per_label' => $copiesPerLabel,
                'total_labels' => $labels->count(),
            ],
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'summary' => [
                'unique_count' => count($accessions),
                'copies_per_label' => $copiesPerLabel,
                'total_labels' => $labels->count(),
                'page_size' => $settings['page_size'],
                'orientation' => $settings['orientation'],
                'columns' => $settings['columns'],
                'label_size' => $settings['label_size'],
            ],
            'accessions' => $accessions,
            'skipped_now' => $skippedNow,
        ]);
    }

    private function barcodeLabels(Collection $accessions, Code128Barcode $barcode): Collection
    {
        return $accessions->map(fn (string $accession): array => [
            'accession' => $accession,
            'svg' => $barcode->svg($accession),
        ])->values();
    }

    private function copyPayload(BookCopy $copy): array
    {
        return [
            'id' => $copy->id,
            'accession' => $copy->accession_number,
            'book_title' => $copy->book?->title ?? 'Unknown title',
            'isbn' => $copy->book?->isbn,
            'status' => $copy->status,
            'condition' => $copy->condition,
            'book_type' => $copy->book_type,
            'shelf_location' => $copy->shelf_location,
            'entry_date' => $copy->entry_date?->format('Y-m-d'),
        ];
    }

    private function hasCopyFilters(array $filters): bool
    {
        return collect(['status', 'book_type', 'condition'])
            ->contains(fn (string $filter) => ! empty($filters[$filter]));
    }

    private function applyCopyFilters($query, array $filters): void
    {
        foreach (['status', 'book_type', 'condition'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }
    }

    private function printRules(): array
    {
        $maximum = (int) config('accession-labels.max_per_batch', 500);

        return [
            'source' => ['required', Rule::in(['generated', 'reprint'])],
            'method' => ['required_if:source,generated', 'nullable', Rule::in(['quantity', 'range'])],
            'start' => ['nullable', 'regex:/^ACC-\d{6}$/'],
            'quantity' => ['nullable', 'integer', 'min:1', "max:{$maximum}"],
            'skip_existing' => ['nullable', 'boolean'],
            'from' => ['nullable', 'regex:/^ACC-\d{6}$/'],
            'to' => ['nullable', 'regex:/^ACC-\d{6}$/'],
            'print_scope' => ['required_if:source,generated', 'nullable', Rule::in(['all', 'selected'])],
            'accessions' => ['nullable', 'array', "max:{$maximum}"],
            'accessions.*' => ['string', 'distinct', 'regex:/^ACC-\d{6}$/'],
            'copy_ids' => ['required_if:source,reprint', 'nullable', 'array', 'min:1', "max:{$maximum}"],
            'copy_ids.*' => ['integer', 'distinct'],
            'label_size' => ['required', Rule::in(array_keys(config('accession-labels.label_sizes')))],
            'columns' => ['required', 'integer', 'between:2,5'],
            'page_size' => ['required', Rule::in(config('accession-labels.page_sizes'))],
            'orientation' => ['required', Rule::in(['portrait', 'landscape'])],
            'barcode_height' => ['required', 'integer', 'between:40,120'],
            'copies_per_label' => ['required', 'integer', 'between:1,10'],
            'show_accession' => ['nullable', 'boolean'],
            'show_library_name' => ['nullable', 'boolean'],
            'show_logo' => ['nullable', 'boolean'],
            'show_border' => ['nullable', 'boolean'],
        ];
    }

    private function printSettings(array $validated): array
    {
        $preset = config("accession-labels.label_sizes.{$validated['label_size']}");

        return [
            'label_size' => $validated['label_size'],
            'columns' => min((int) $validated['columns'], (int) $preset['max_columns']),
            'page_size' => $validated['page_size'],
            'orientation' => $validated['orientation'],
            'barcode_height' => (int) $validated['barcode_height'],
            'show_accession' => (bool) ($validated['show_accession'] ?? false),
            'show_library_name' => (bool) ($validated['show_library_name'] ?? false),
            'show_logo' => (bool) ($validated['show_logo'] ?? false),
            'show_border' => (bool) ($validated['show_border'] ?? false),
        ];
    }
}
