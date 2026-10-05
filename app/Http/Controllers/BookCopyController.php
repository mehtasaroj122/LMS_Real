<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Services\PhysicalBookCopyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookCopyController extends Controller
{
    public function index(Book $book)
    {
        $book->load(['category', 'copies' => fn ($query) => $query->orderBy('accession_number')]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'book' => $book,
                    'copies' => $book->copies,
                    'summary' => $this->summary($book),
                ],
            ]);
        }

        return view($this->viewPrefix() . '.BookCopies', compact('book'));
    }

    public function store(Request $request, Book $book, PhysicalBookCopyService $copies): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'entry_date' => ['nullable', 'date'],
            'book_type' => ['nullable', 'in:borrowing,reference'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'condition' => ['nullable', 'in:new,good,fair,damaged,lost'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $created = $copies->createCopies($book, (int) $validated['quantity'], $validated);

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
            'entry_date' => ['sometimes', 'date'],
            'book_type' => ['sometimes', 'in:borrowing,reference'],
            'status' => ['sometimes', 'in:available,issued,lost,damaged'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'condition' => ['sometimes', 'in:new,good,fair,damaged,lost'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        unset($validated['status']);
        $bookCopy->update($validated);
        $copies->refreshBookCounters($bookCopy->book);

        return response()->json(['success' => true, 'data' => ['copy' => $bookCopy->fresh('book')]]);
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

    private function viewPrefix(): string
    {
        return auth()->user()?->role === 'admin' ? 'Admin' : 'Staff';
    }
}
