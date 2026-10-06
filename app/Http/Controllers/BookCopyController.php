<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Services\PhysicalBookCopyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookCopyController extends Controller
{
    /**
     * Search existing general book records for the Add Physical Book selector.
     * The result is intentionally capped so the page never loads the whole
     * catalogue into the browser.
     */
    public function searchBooks(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));

        $query = Book::query()
            ->select(['id', 'title', 'isbn', 'author'])
            ->when($term !== '', function ($query) use ($term): void {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(function ($search) use ($like): void {
                    $search->where('title', 'like', $like)
                        ->orWhere('isbn', 'like', $like)
                        ->orWhere('author', 'like', $like);
                });
            })
            ->orderBy('title')
            ->limit(20);

        $books = $query->get()->map(fn (Book $book): array => [
            'book_id' => $book->id,
            'title' => $book->title,
            'isbn' => $book->isbn,
            'author' => $book->author,
        ])->values();

        return response()->json([
            'success' => true,
            'has_books' => Book::query()->exists(),
            'data' => $books,
        ]);
    }

    /**
     * Display a non-reserving accession range preview for the Add Physical
     * Book batch form. Saved values are always allocated again by the backend
     * inside the create transaction.
     */
    public function previewNextAccession(Request $request, PhysicalBookCopyService $copies): JsonResponse
    {
        $validated = $this->validateBatchInput($request, false);
        $book = Book::query()->findOrFail($validated['book_id']);
        $accessions = $copies->previewAccessions((int) $validated['total_copies']);

        return response()->json([
            'success' => true,
            'data' => [
                'book' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                    'author' => $book->author,
                ],
                'total_copies' => (int) $validated['total_copies'],
                'borrowing_copies' => (int) $validated['borrowing_copies'],
                'reference_copies' => (int) $validated['reference_copies'],
                'accession_from' => $accessions[0] ?? null,
                'accession_to' => $accessions[count($accessions) - 1] ?? null,
            ],
        ]);
    }

    /**
     * Add a borrowing/reference batch to an existing general book record.
     * This endpoint deliberately accepts book_id only; a typed title or a
     * client-supplied accession number cannot create a relationship.
     */
    public function storeSelected(Request $request, PhysicalBookCopyService $copies): JsonResponse
    {
        $validated = $this->validateBatchInput($request);

        $book = Book::query()->findOrFail($validated['book_id']);
        $created = $copies->createCopyBatch(
            $book,
            (int) $validated['borrowing_copies'],
            (int) $validated['reference_copies'],
            $validated
        );
        $created->each(fn (BookCopy $copy) => $copy->load('book'));
        $first = $created->first();
        $last = $created->last();

        return response()->json([
            'success' => true,
            'message' => $created->count() . ' physical copies added successfully.',
            'data' => [
                'book' => $book->fresh(),
                'copies' => $created,
                'accession_range' => [
                    'from' => $first?->accession_number,
                    'to' => $last?->accession_number,
                ],
                'status' => 'available',
            ],
        ], 201);
    }

    private function validateBatchInput(Request $request, bool $includePhysicalFields = true): array
    {
        $rules = [
            'book_id' => ['bail', 'required', 'integer', 'exists:books,id'],
            'accession_number' => ['prohibited'],
            'total_copies' => ['required', 'integer', 'min:1', 'max:500'],
            'borrowing_copies' => ['required', 'integer', 'min:0', 'max:500'],
            'reference_copies' => ['required', 'integer', 'min:0', 'max:500'],
        ];

        if ($includePhysicalFields) {
            $rules += [
                'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                'entry_date' => ['required', 'date', 'before_or_equal:today'],
                'shelf_location' => ['nullable', 'string', 'max:100'],
                'condition' => ['required', 'in:new,good,fair,damaged'],
                'remarks' => ['nullable', 'string', 'max:1000'],
            ];
        }

        $validator = Validator::make($request->all(), $rules, [
            'book_id.exists' => 'Selected book does not exist.',
            'book_id.required' => 'Select an existing book.',
            'accession_number.prohibited' => 'Accession number is generated automatically.',
            'entry_date.before_or_equal' => 'Entry date cannot be later than today.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $total = (int) $request->input('total_copies', -1);
            $borrowing = (int) $request->input('borrowing_copies', -1);
            $reference = (int) $request->input('reference_copies', -1);

            if ($total >= 0 && $borrowing >= 0 && $reference >= 0 && $total !== ($borrowing + $reference)) {
                $validator->errors()->add(
                    'total_copies',
                    'Borrowing and Reference copies must equal the total number of copies.'
                );
            }
        });

        return $validator->validate();
    }

    public function index(Book $book)
    {
        $summary = $this->summary($book);

        if (request()->expectsJson() && ! request()->boolean('ajax_copies')) {
            $book->load([
                'category',
                'copies' => function ($query): void {
                    $query->with(['issuedBooks' => fn ($issueQuery) => $issueQuery
                        ->whereNull('return_date')
                        ->latest('issue_date')
                        ->with('student.user')]);
                    $this->applyCopyListingOrder($query);
                },
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'book' => $book,
                    'copies' => $book->copies,
                    'summary' => $summary,
                ],
            ]);
        }

        $request = request();
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $status = in_array($request->query('status'), ['available', 'issued', 'lost', 'damaged'], true)
            ? $request->query('status') : 'all';
        $bookType = in_array($request->query('book_type'), ['borrowing', 'reference'], true)
            ? $request->query('book_type') : 'all';
        $perPage = in_array((int) $request->query('per_page', 10), [10, 20, 50, 100], true)
            ? (int) $request->query('per_page', 10) : 10;

        $copiesQuery = BookCopy::query()
            ->where('book_id', $book->id)
            ->with(['issuedBooks' => fn ($issueQuery) => $issueQuery
                ->whereNull('return_date')
                ->latest('issue_date')
                ->with('student.user')]);

        if ($status !== 'all') {
            $copiesQuery->where('status', $status);
        }
        if ($bookType !== 'all') {
            $copiesQuery->where('book_type', $bookType);
        }
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $copiesQuery->where(function ($query) use ($like): void {
                $query->where('accession_number', 'like', $like)
                    ->orWhere('shelf_location', 'like', $like)
                    ->orWhere('remarks', 'like', $like)
                    ->orWhereHas('issuedBooks', fn ($issueQuery) => $issueQuery
                        ->whereNull('return_date')
                        ->whereHas('student', fn ($studentQuery) => $studentQuery
                            ->where('student_id', 'like', $like)
                            ->orWhere('roll_no', 'like', $like)
                            ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $like))));
            });
        }

        $this->applyCopyListingOrder($copiesQuery);
        $copies = $copiesQuery->paginate($perPage);
        if ($copies->total() > 0 && $copies->currentPage() > $copies->lastPage()) {
            if (! $request->boolean('ajax_copies')) {
                return redirect()->to($request->fullUrlWithQuery(['page' => $copies->lastPage()]));
            }
            $copies = $copiesQuery->paginate($perPage, ['*'], 'page', $copies->lastPage());
        }
        $copies->appends($request->except('ajax_copies'));

        if ($request->boolean('ajax_copies')) {
            return response()->json([
                'success' => true,
                'tableRows' => view('shared.book-copies.rows', compact('copies', 'search', 'status', 'bookType'))->render(),
                'pagination' => view('shared.admin-table-pagination', ['paginator' => $copies])->render(),
                'current_page' => $copies->currentPage(),
                'last_page' => $copies->lastPage(),
                'per_page' => $perPage,
                'total' => $copies->total(),
                'summary' => $summary,
            ]);
        }

        return view($this->viewPrefix() . '.BookCopies', compact('book', 'summary', 'copies', 'search', 'status', 'bookType', 'perPage'));
    }

    public function preview(Request $request, Book $book, PhysicalBookCopyService $copies): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'book_id' => $book->id,
                'accessions' => $copies->previewAccessions((int) $validated['quantity']),
            ],
        ]);
    }

    public function store(Request $request, Book $book, PhysicalBookCopyService $copies): JsonResponse
    {
        $mode = $request->input('mode', 'single');

        if ($mode === 'multiple') {
            $validator = Validator::make($request->all(), [
                'mode' => ['required', 'in:multiple'],
                'total_copies' => ['required', 'integer', 'min:1', 'max:500'],
                'borrowing_copies' => ['required', 'integer', 'min:0', 'max:500'],
                'reference_copies' => ['required', 'integer', 'min:0', 'max:500'],
                'book_type' => ['prohibited'],
                'entry_date' => ['nullable', 'date', 'before_or_equal:today'],
                'shelf_location' => ['nullable', 'string', 'max:100'],
                'condition' => ['required', 'in:new,good,fair,damaged'],
                'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                'remarks' => ['nullable', 'string', 'max:1000'],
            ], [
                'book_type.prohibited' => 'Book type is selected by the borrowing and reference copy counts.',
            ]);
            $validator->after(function ($validator) use ($request): void {
                $total = (int) $request->input('total_copies', -1);
                $borrowing = (int) $request->input('borrowing_copies', -1);
                $reference = (int) $request->input('reference_copies', -1);

                if ($total >= 0 && $borrowing >= 0 && $reference >= 0 && $total !== ($borrowing + $reference)) {
                    $validator->errors()->add(
                        'total_copies',
                        'Borrowing and Reference copies must equal the total number of copies.'
                    );
                }
            });
            $validated = $validator->validate();
            $created = $copies->createCopyBatch(
                $book,
                (int) $validated['borrowing_copies'],
                (int) $validated['reference_copies'],
                $validated
            );
        } else {
            $validated = $request->validate([
                'mode' => ['sometimes', 'in:single'],
                'quantity' => ['required', 'integer', 'min:1', 'max:500'],
                'accession_number' => ['prohibited'],
                'entry_date' => ['nullable', 'date', 'before_or_equal:today'],
                'book_type' => ['required', 'in:borrowing,reference'],
                'shelf_location' => ['nullable', 'string', 'max:100'],
                'condition' => ['required', 'in:new,good,fair,damaged'],
                'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                'remarks' => ['nullable', 'string', 'max:1000'],
            ], [
                'accession_number.prohibited' => 'Accession number is generated automatically.',
            ]);
            $created = $copies->createCopies($book, (int) $validated['quantity'], $validated);
        }

        return response()->json([
            'success' => true,
            'message' => $created->count() . ' physical copy/copies added successfully.',
            'data' => [
                'copies' => $created,
                'summary' => $this->summary($book->fresh()),
            ],
        ], 201);
    }

    public function show(string $accessionNumber, PhysicalBookCopyService $copies): JsonResponse
    {
        $copy = $copies->findByAccession($accessionNumber, true);

        return $copy
            ? response()->json(['success' => true, 'data' => ['copy' => $copy]])
            : response()->json(['success' => false, 'message' => 'Accession number not found.'], 404);
    }

    public function update(Request $request, BookCopy $bookCopy, PhysicalBookCopyService $copies): JsonResponse
    {
        if (in_array($bookCopy->status, ['issued'], true) && $request->filled('status') && $request->input('status') !== 'issued') {
            return response()->json(['success' => false, 'message' => 'An issued copy must be returned before its status can change.'], 409);
        }

        $validated = $request->validate([
            'entry_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'book_type' => ['sometimes', 'in:borrowing,reference'],
            'status' => ['sometimes', 'in:available,issued,lost,damaged'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'condition' => ['sometimes', 'in:new,good,fair,damaged,lost'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        unset($validated['status']);
        $bookCopy->update($validated);
        $copies->refreshBookCounters($bookCopy->book);

        return response()->json(['success' => true, 'data' => ['copy' => $bookCopy->fresh('book')]]);
    }

    public function destroy(BookCopy $bookCopy): JsonResponse
    {
        $hasHistory = $bookCopy->issuedBooks()->exists();

        if ($hasHistory) {
            return response()->json([
                'success' => false,
                'message' => 'This physical copy cannot be deleted because it has borrowing history.',
            ], 422);
        }

        DB::transaction(function () use ($bookCopy): void {
            $book = $bookCopy->book()->lockForUpdate()->firstOrFail();
            $bookCopy->delete();
            app(PhysicalBookCopyService::class)->refreshBookCounters($book);
        });

        return response()->json([
            'success' => true,
            'message' => 'Physical copy deleted successfully.',
        ]);
    }

    private function summary(Book $book): array
    {
        $counts = $book->copies()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'available' => (int) ($counts['available'] ?? 0),
            'issued' => (int) ($counts['issued'] ?? 0),
            'lost' => (int) ($counts['lost'] ?? 0),
            'damaged' => (int) ($counts['damaged'] ?? 0),
        ];
    }

    private function applyCopyListingOrder($query): void
    {
        if (auth()->user()?->role === 'admin') {
            $query->orderByDesc('created_at')->orderByDesc('id');
        } else {
            $query->orderBy('accession_number');
        }
    }

    private function viewPrefix(): string
    {
        return auth()->user()?->role === 'admin' ? 'Admin' : 'Staff';
    }
}
