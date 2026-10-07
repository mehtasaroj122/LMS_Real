<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateAccessionLabelsRequest;
use App\Models\BookCopy;
use App\Services\AccessionNumberGenerator;
use App\Services\Code128Barcode;
use App\Support\LibraryBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccessionLabelController extends Controller
{
    public function index(Request $request, AccessionNumberGenerator $generator)
    {
        Gate::authorize('access-admin');

        $mode = $request->string('mode')->toString() === 'reprint' ? 'reprint' : 'generate';
        $search = trim((string) $request->query('q', ''));
        $copies = collect();

        if ($mode === 'reprint' && $search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $copies = BookCopy::query()
                ->with('book:id,title,isbn')
                ->where(function ($query) use ($like): void {
                    $query->where('accession_number', 'like', $like)
                        ->orWhereHas('book', fn ($bookQuery) => $bookQuery
                            ->where('title', 'like', $like)
                            ->orWhere('isbn', 'like', $like));
                })
                ->orderBy('accession_number')
                ->limit(20)
                ->get();
        }

        return view('Admin.AccessionLabels.index', $this->pageData($generator, [
            'mode' => $mode,
            'search' => $search,
            'copies' => $copies,
        ]));
    }

    public function preview(
        GenerateAccessionLabelsRequest $request,
        AccessionNumberGenerator $generator,
        Code128Barcode $barcode
    ) {
        Gate::authorize('access-admin');

        $validated = $request->validated();
        $requested = $generator->range($validated['from'], $validated['to']);
        $existing = BookCopy::query()
            ->whereIn('accession_number', $requested)
            ->orderBy('accession_number')
            ->pluck('accession_number')
            ->all();
        $existingLookup = array_fill_keys($existing, true);
        $available = array_values(array_filter(
            $requested,
            fn (string $accession) => ! isset($existingLookup[$accession])
        ));

        $labels = collect($available)->map(fn (string $accession): array => [
            'accession' => $accession,
            'svg' => $barcode->svg($accession),
        ]);

        ActivityLogger::logActivity(
            'accession_barcodes_generated',
            sprintf(
                '%s generated %d accession barcode label%s from %s to %s.',
                $request->user()->name,
                count($available),
                count($available) === 1 ? '' : 's',
                $validated['from'],
                $validated['to']
            ),
            'book',
            'accession_labels',
            null,
            [
                'from_accession' => $validated['from'],
                'to_accession' => $validated['to'],
                'requested_count' => count($requested),
                'generated_count' => count($available),
                'skipped_count' => count($existing),
                'action_type' => 'generate_preview',
                'admin_id' => $request->user()->id,
            ]
        );

        return view('Admin.AccessionLabels.index', $this->pageData($generator, [
            'mode' => 'generate',
            'search' => '',
            'copies' => collect(),
            'preview' => [
                'from' => $validated['from'],
                'to' => $validated['to'],
                'requested' => count($requested),
                'available' => count($available),
                'existing' => $existing,
                'labels' => $labels,
            ],
        ]));
    }

    public function print(Request $request, AccessionNumberGenerator $generator, Code128Barcode $barcode)
    {
        Gate::authorize('access-admin');

        $validator = Validator::make($request->all(), [
            'action_type' => ['required', Rule::in(['range', 'reprint'])],
            'from' => ['required_if:action_type,range', 'nullable', 'regex:/^ACC-\d{6}$/'],
            'to' => ['required_if:action_type,range', 'nullable', 'regex:/^ACC-\d{6}$/'],
            'print_scope' => ['required_if:action_type,range', 'nullable', Rule::in(['all', 'selected'])],
            'labels' => ['nullable', 'array', 'max:'.config('accession-labels.max_per_batch', 500)],
            'labels.*' => ['string', 'distinct', 'regex:/^ACC-\d{6}$/'],
            'copy_id' => ['required_if:action_type,reprint', 'nullable', 'integer', 'exists:book_copies,id'],
            'label_size' => ['required', Rule::in(array_keys(config('accession-labels.label_sizes')))],
            'columns' => ['required', 'integer', 'between:2,5'],
            'page_size' => ['required', Rule::in(config('accession-labels.page_sizes'))],
            'show_accession' => ['nullable', 'boolean'],
            'show_library_name' => ['nullable', 'boolean'],
            'show_logo' => ['nullable', 'boolean'],
            'show_border' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->printError($validator->errors()->first());
        }

        $validated = $validator->validated();

        $settings = $this->validatedPrintSettings($validated);
        $copy = null;

        if ($validated['action_type'] === 'reprint') {
            $copy = BookCopy::query()->with('book:id,title,isbn')->findOrFail($validated['copy_id']);
            $accessions = [$copy->accession_number];
        } else {
            $start = $generator->parse((string) $validated['from']);
            $end = $generator->parse((string) $validated['to']);
            $maximum = (int) config('accession-labels.max_per_batch', 500);

            if ($start === null || $end === null || $end < $start || ($end - $start + 1) > $maximum) {
                return $this->printError('The accession range is invalid or exceeds the batch limit.');
            }

            $range = $generator->range($validated['from'], $validated['to']);
            $existing = BookCopy::query()->whereIn('accession_number', $range)->pluck('accession_number')->all();
            $available = array_values(array_diff($range, $existing));

            if ($validated['print_scope'] === 'selected') {
                $selected = array_values(array_unique(array_map('strtoupper', $validated['labels'] ?? [])));
                $accessions = array_values(array_intersect($available, $selected));
            } else {
                $accessions = $available;
            }

            if ($accessions === []) {
                return $this->printError('No printable accession numbers remain. Generate the preview again to refresh availability.');
            }
        }

        $labels = collect($accessions)->map(fn (string $accession): array => [
            'accession' => $accession,
            'svg' => $barcode->svg($accession, 64),
            'book_title' => $copy?->book?->title,
        ]);

        $action = $validated['action_type'] === 'reprint'
            ? 'accession_barcode_reprinted'
            : 'accession_barcodes_printed';
        $description = $validated['action_type'] === 'reprint'
            ? sprintf('%s reprinted the barcode label for %s.', $request->user()->name, $copy->accession_number)
            : sprintf('%s prepared %d accession barcode labels for printing.', $request->user()->name, count($accessions));

        ActivityLogger::logActivity($action, $description, 'book', 'accession_labels', $copy?->id, [
            'from_accession' => $accessions[0] ?? null,
            'to_accession' => $accessions[count($accessions) - 1] ?? null,
            'printed_count' => count($accessions),
            'action_type' => $validated['action_type'] === 'reprint' ? 'reprint' : 'print',
            'admin_id' => $request->user()->id,
            'accession_number' => $copy?->accession_number,
            'book_id' => $copy?->book_id,
            'copy_id' => $copy?->id,
        ]);

        return view('Admin.AccessionLabels.print', [
            'labels' => $labels,
            'settings' => $settings,
            'branding' => LibraryBranding::resolve(),
            'labelPreset' => config("accession-labels.label_sizes.{$settings['label_size']}"),
        ]);
    }

    private function pageData(AccessionNumberGenerator $generator, array $data = []): array
    {
        return array_merge([
            'nextAvailable' => $generator->nextAvailable(),
            'maximum' => (int) config('accession-labels.max_per_batch', 500),
            'labelSizes' => config('accession-labels.label_sizes'),
            'defaults' => [
                'label_size' => config('accession-labels.default_label_size', 'medium'),
                'columns' => (int) config('accession-labels.default_columns', 3),
                'page_size' => config('accession-labels.default_page_size', 'A4'),
            ],
            'branding' => LibraryBranding::resolve(),
            'preview' => null,
        ], $data);
    }

    private function validatedPrintSettings(array $validated): array
    {
        $preset = config("accession-labels.label_sizes.{$validated['label_size']}");
        $columns = min((int) $validated['columns'], (int) $preset['max_columns']);

        return [
            'label_size' => $validated['label_size'],
            'columns' => $columns,
            'page_size' => $validated['page_size'],
            'show_accession' => (bool) ($validated['show_accession'] ?? false),
            'show_library_name' => (bool) ($validated['show_library_name'] ?? false),
            'show_logo' => (bool) ($validated['show_logo'] ?? false),
            'show_border' => (bool) ($validated['show_border'] ?? false),
        ];
    }

    private function printError(string $message)
    {
        return response()->view('Admin.AccessionLabels.print-error', [
            'message' => $message,
        ], 422);
    }
}
