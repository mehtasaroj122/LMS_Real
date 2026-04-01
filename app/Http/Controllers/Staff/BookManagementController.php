<?php

namespace App\Http\Controllers\Staff;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookStoreRequest;
use App\Models\book;
use App\Models\category;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookManagementController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('access-staff');

        $categories = category::orderBy('name')->get();
        [
            'search' => $search,
            'condition' => $condition,
            'category' => $selectedCategory,
            'availability' => $availability,
            'sort' => $sort,
            'page' => $page,
            'per_page' => $perPage,
        ] = $this->normalizeBookListFilters($request);

        $initialBooksQuery = $this->buildFilteredBooksQuery($search, $condition, $selectedCategory, $availability);
        $this->applyBookSorting($initialBooksQuery, $sort);

        $initialBooks = $initialBooksQuery
            ->with('category')
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends($request->query());

        $initialStats = $this->calculateBookStats(
            $this->buildFilteredBooksQuery($search, $condition, $selectedCategory, $availability)
        );

        return view('Staff.BookManagement', compact(
            'categories',
            'initialBooks',
            'initialStats',
            'search',
            'condition',
            'selectedCategory',
            'availability',
            'sort',
            'perPage'
        ));
    }

    public function getBooksData(Request $request)
    {
        Gate::authorize('access-staff');

        [
            'search' => $search,
            'condition' => $condition,
            'category' => $category,
            'availability' => $availability,
            'sort' => $sort,
            'page' => $page,
            'per_page' => $perPage,
        ] = $this->normalizeBookListFilters($request);

        $query = $this->buildFilteredBooksQuery($search, $condition, $category, $availability);
        $this->applyBookSorting($query, $sort);

        $books = $query
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends($request->query());
        $books->load('category');

        $tableRows = '';
        foreach ($books->items() as $book) {
            $conditionClass = $book->condition === 'new'
                ? 'condition-new'
                : ($book->condition === 'damaged' ? 'condition-damaged' : 'condition-good');
            $conditionIcon = $book->condition === 'new'
                ? 'fa-star'
                : ($book->condition === 'damaged' ? 'fa-exclamation-triangle' : 'fa-check-circle');
            $conditionText = ucfirst($book->condition);

            $tableRows .= '<tr'
                . ' data-book-id="' . $book->id . '"'
                . ' data-category-id="' . ($book->category->id ?? '') . '"'
                . ' data-category-name="' . htmlspecialchars($book->category->name ?? 'N/A') . '"'
                . ' data-condition="' . $book->condition . '"'
                . ' data-cover="' . htmlspecialchars($book->cover_image ?? '') . '"'
                . ' data-description="' . htmlspecialchars($book->description ?? '') . '"'
                . ' data-publisher="' . htmlspecialchars($book->publisher ?? '') . '"'
                . ' data-isbn="' . htmlspecialchars($book->isbn ?? '') . '"'
                . ' data-shelf="' . htmlspecialchars($book->shelf_no ?? '') . '"'
                . ' data-total-copies="' . htmlspecialchars($book->total_copies ?? 0) . '"'
                . ' data-available-copies="' . htmlspecialchars($book->available_copies ?? 0) . '"'
                . '>';
            $tableRows .= '<td>' . htmlspecialchars($book->isbn) . '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<div style="display: flex; align-items: center; gap: 12px;">';

            if ($book->cover_image) {
                $imageUrl = str_starts_with($book->cover_image, 'http')
                    ? $book->cover_image
                    : asset('storage/' . $book->cover_image);
                $tableRows .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="'
                    . htmlspecialchars($book->title)
                    . '" style="width: 40px; height: 50px; border-radius: 4px; object-fit: cover; border: 1px solid #e5e7eb;">';
            } else {
                $firstLetter = strtoupper(substr($book->title, 0, 1));
                $tableRows .= '<div style="width: 40px; height: 50px; min-width: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #ffffff; font-weight: 700; font-size: 20px; flex-shrink: 0;">';
                $tableRows .= $firstLetter;
                $tableRows .= '</div>';
            }

            $tableRows .= '<div>';
            $tableRows .= '<strong style="font-size: 13px;">' . htmlspecialchars($book->title) . '</strong>';
            $tableRows .= '<div style="font-size: 10px; color: #9ca3af; margin-top: 1px;">'
                . htmlspecialchars($book->publisher ?? 'Unknown')
                . '</div>';
            $tableRows .= '</div>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '<td>' . htmlspecialchars($book->author) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($book->category->name ?? 'N/A') . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($book->shelf_no ?? 'N/A') . '</td>';
            $tableRows .= '<td><div class="copy-count"><span class="copy-total">' . $book->total_copies . '</span></div></td>';
            $tableRows .= '<td><div class="copy-count"><span class="copy-available">' . $book->available_copies . '</span></div></td>';
            $tableRows .= '<td>';
            $tableRows .= '<span class="condition-badge ' . $conditionClass . '">';
            $tableRows .= '<i class="fas ' . $conditionIcon . '"></i>';
            $tableRows .= $conditionText;
            $tableRows .= '</span>';
            $tableRows .= '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<div class="action-buttons">';
            $tableRows .= '<button class="action-btn view" title="View Details" aria-label="View book details"><i class="fas fa-eye"></i></button>';
            $tableRows .= '<button class="action-btn edit" title="Edit Book" aria-label="Edit book"><i class="fas fa-edit"></i></button>';
            $tableRows .= '<button class="action-btn delete" title="Request Deletion" aria-label="Request book deletion"><i class="fas fa-trash-alt"></i></button>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '</tr>';
        }

        $filteredStats = $this->calculateBookStats(
            $this->buildFilteredBooksQuery($search, $condition, $category, $availability)
        );

        return response()->json([
            'success' => true,
            'tableRows' => $tableRows,
            'pagination' => view('shared.admin-table-pagination', ['paginator' => $books])->render(),
            'stats' => $filteredStats,
            'total' => $books->total(),
            'current_page' => $books->currentPage(),
            'last_page' => $books->lastPage(),
            'per_page' => $books->perPage(),
        ]);
    }

    public function getBookStats(Request $request = null)
    {
        Gate::authorize('access-staff');

        [
            'search' => $search,
            'condition' => $condition,
            'category' => $category,
            'availability' => $availability,
        ] = $this->normalizeBookListFilters($request);

        $stats = $this->calculateBookStats(
            $this->buildFilteredBooksQuery($search, $condition, $category, $availability)
        );

        if ($request && $request->expectsJson()) {
            return response()->json($stats);
        }

        return $stats;
    }

    public function validateField(Request $request)
    {
        Gate::authorize('access-staff');

        $field = (string) $request->input('field');
        $allowedFields = [
            'isbn',
            'shelf_no',
            'title',
            'author',
            'publisher',
            'category_id',
            'new_category',
            'total_copies',
            'available_copies',
            'condition',
            'description',
            'cover_image',
        ];

        if (!in_array($field, $allowedFields, true)) {
            return response()->json([
                'valid' => false,
                'message' => 'Unsupported validation field.',
            ], 422);
        }

        $data = $this->normalizeBookValidationInput($request->all());
        $bookId = $request->input('book_id');
        $rules = BookStoreRequest::rulesFor($bookId);
        $messages = BookStoreRequest::validationMessages();

        $validator = Validator::make($data, [$field => $rules[$field]], $messages);
        $this->attachBookValidationCallbacks($validator, $data, $field);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'field' => $field,
                'message' => $validator->errors()->first($field),
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'field' => $field,
            'message' => null,
        ]);
    }

    public function store(BookStoreRequest $request)
    {
        Gate::authorize('access-staff');

        try {
            $validated = $request->validated();

            $categoryId = !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
            $newCategoryName = !empty($validated['new_category']) ? trim($validated['new_category']) : null;

            if (!$categoryId && !$newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select an existing category or create a new one.',
                    'errors' => ['category_id' => ['Select an existing category or create a new one.']],
                ], 422);
            }

            if ($categoryId && $newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Choose either an existing category or a new category, not both.',
                    'errors' => ['new_category' => ['Choose either an existing category or a new category, not both.']],
                ], 422);
            }

            if (!$categoryId && $newCategoryName) {
                $categoryModel = category::firstOrCreate(['name' => $newCategoryName]);
                $categoryId = $categoryModel->id;
            }

            $validated['category_id'] = $categoryId;
            unset($validated['new_category']);

            if (empty($validated['category_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category ID is required',
                ], 422);
            }

            $book = book::create($validated);

            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('books/covers', 'public');
                $book->cover_image = $path;
                $book->save();
            }

            $category = category::find($validated['category_id']);

            ActivityLogger::logActivity(
                'book_created',
                'Created Book: ' . $book->title . ' (Category: ' . ($category->name ?? 'N/A') . ')',
                'book',
                'book',
                $book->id,
                ['category_id' => $validated['category_id'], 'category_name' => $category->name ?? 'N/A']
            );

            $students = User::where('role', 'student')->get();
            foreach ($students as $student) {
                Notification::notify(
                    user: $student,
                    type: 'book.added',
                    title: 'New Book Available',
                    message: "'{$book->title}' has been added to the library",
                    data: [
                        'book_id' => $book->id,
                        'category_id' => $book->category_id,
                        'category_name' => $category->name ?? 'N/A',
                    ],
                    relatedModel: 'Book',
                    relatedId: $book->id
                );
            }

            if ($book->available_copies < 5) {
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    Notification::notify(
                        user: $admin,
                        type: 'book.low_inventory',
                        title: 'Low Stock Alert',
                        message: "New book '{$book->title}' added with only {$book->available_copies} copy(ies)",
                        data: [
                            'book_id' => $book->id,
                            'available_copies' => $book->available_copies,
                            'title' => $book->title,
                        ],
                        relatedModel: 'Book',
                        relatedId: $book->id
                    );
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book created successfully',
                    'book' => $book,
                ]);
            }

            return redirect()->route('staff.books.index')->with('success', 'Book created successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating book: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(BookStoreRequest $request, string $id)
    {
        Gate::authorize('access-staff');

        try {
            $book = book::findOrFail($id);
            $validated = $request->validated();

            $categoryId = !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
            $newCategoryName = !empty($validated['new_category']) ? trim($validated['new_category']) : null;

            if (!$categoryId && !$newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select an existing category or create a new one.',
                    'errors' => ['category_id' => ['Select an existing category or create a new one.']],
                ], 422);
            }

            if ($categoryId && $newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Choose either an existing category or a new category, not both.',
                    'errors' => ['new_category' => ['Choose either an existing category or a new category, not both.']],
                ], 422);
            }

            if (!$categoryId && $newCategoryName) {
                $categoryModel = category::firstOrCreate(['name' => $newCategoryName]);
                $categoryId = $categoryModel->id;
            }

            $validated['category_id'] = $categoryId;
            unset($validated['new_category']);

            $oldCondition = $book->condition;
            $oldCopies = $book->available_copies;

            $book->update($validated);

            if ($request->hasFile('cover_image')) {
                if (!empty($book->cover_image) && Storage::disk('public')->exists($book->cover_image)) {
                    Storage::disk('public')->delete($book->cover_image);
                }

                $path = $request->file('cover_image')->store('books/covers', 'public');
                $book->cover_image = $path;
                $book->save();
            }

            $category = category::find($validated['category_id']);
            $changes = [];
            if ($oldCondition !== $book->condition) {
                $changes[] = "condition from {$oldCondition} to {$book->condition}";
            }
            $changeDetails = !empty($changes) ? ' (' . implode(', ', $changes) . ')' : '';

            ActivityLogger::logActivity(
                'book_updated',
                'Updated Book: ' . $book->title . ' (Category: ' . ($category->name ?? 'N/A') . ')' . $changeDetails,
                'book',
                'book',
                $book->id,
                ['changes' => $changes, 'category_id' => $book->category_id]
            );

            if ($book->available_copies < 5 && $oldCopies >= 5) {
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    Notification::notify(
                        user: $admin,
                        type: 'book.low_inventory',
                        title: 'Low Book Inventory Alert',
                        message: "{$book->title} now has only {$book->available_copies} copy(ies) remaining in stock",
                        data: [
                            'book_id' => $book->id,
                            'available_copies' => $book->available_copies,
                            'title' => $book->title,
                        ],
                        relatedModel: 'Book',
                        relatedId: $book->id
                    );
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book updated successfully',
                    'book' => $book,
                ]);
            }

            return redirect()->route('staff.books.index')->with('success', 'Book updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating book: ' . $e->getMessage(),
                ], 500);
            }

            throw $e;
        }
    }

    public function destroy(string $id)
    {
        Gate::authorize('access-staff');

        $message = 'Staff members cannot delete books directly. Submit a deletion request instead.';

        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()->route('staff.books.index')->with('error', $message);
    }

    public function createCategory(Request $request)
    {
        Gate::authorize('access-staff');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        try {
            $category = category::create($validated);

            ActivityLogger::logActivity(
                'category_created',
                'Created Category: ' . $category->name,
                'category',
                'category',
                $category->id,
                ['category_name' => $category->name]
            );

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating category: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function normalizeBookValidationInput(array $data): array
    {
        $normalizeText = function ($value) {
            if ($value === null) {
                return null;
            }

            return trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $value)));
        };

        if (array_key_exists('isbn', $data)) {
            $data['isbn'] = preg_replace('/[\/\-\s]/', '', (string) $data['isbn']);
        }

        foreach (['shelf_no', 'title', 'author', 'publisher', 'new_category', 'description'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $normalizeText($data[$field]);
            }
        }

        if (array_key_exists('total_copies', $data) && $data['total_copies'] !== null && $data['total_copies'] !== '') {
            $data['total_copies'] = (int) $data['total_copies'];
        }

        if (array_key_exists('available_copies', $data) && $data['available_copies'] !== null && $data['available_copies'] !== '') {
            $data['available_copies'] = (int) $data['available_copies'];
        }

        return $data;
    }

    protected function attachBookValidationCallbacks($validator, array $data, ?string $field = null): void
    {
        $validator->after(function ($validator) use ($data, $field) {
            $checkCategory = $field === null || in_array($field, ['category_id', 'new_category'], true);
            $categoryId = trim((string) ($data['category_id'] ?? ''));
            $newCategory = trim((string) ($data['new_category'] ?? ''));

            if ($checkCategory) {
                if ($categoryId === '' && $newCategory === '') {
                    $validator->errors()->add('category_id', 'Select an existing category or create a new one.');
                }

                if ($categoryId !== '' && $newCategory !== '') {
                    $validator->errors()->add('new_category', 'Choose either an existing category or a new category, not both.');
                }
            }
        });
    }

    private function normalizeBookListFilters(?Request $request = null): array
    {
        $request ??= request();

        $condition = (string) ($request?->input('condition') ?? 'all');
        $category = (string) ($request?->input('category') ?? 'all');
        $availability = (string) ($request?->input('availability') ?? 'all');
        $sort = (string) ($request?->input('sort') ?? 'recently-added');

        return [
            'search' => trim((string) ($request?->input('search') ?? '')),
            'condition' => $condition !== '' ? $condition : 'all',
            'category' => $category !== '' ? $category : 'all',
            'availability' => $availability !== '' ? $availability : 'all',
            'sort' => $sort !== '' ? $sort : 'recently-added',
            'page' => max(1, (int) ($request?->input('page') ?? 1)),
            'per_page' => $this->normalizeStaffPerPage($request?->input('per_page') ?? 10),
        ];
    }

    private function normalizeStaffPerPage($value): int
    {
        $allowedValues = [10, 20, 50, 100];
        $perPage = (int) $value;

        return in_array($perPage, $allowedValues, true) ? $perPage : 10;
    }

    private function applyBookSorting($query, ?string $sort): void
    {
        $sort = (string) ($sort ?? 'recently-added');

        switch ($sort) {
            case 'recently-added':
                $query->orderBy('created_at', 'desc');
                break;
            case 'title-asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title-desc':
                $query->orderBy('title', 'desc');
                break;
            case 'author-asc':
                $query->orderBy('author', 'asc');
                break;
            case 'author-desc':
                $query->orderBy('author', 'desc');
                break;
            case 'copies-desc':
                $query->orderBy('total_copies', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
    }

    private function buildFilteredBooksQuery(
        ?string $search = '',
        ?string $condition = 'all',
        ?string $category = 'all',
        ?string $availability = 'all'
    ) {
        $search = trim((string) ($search ?? ''));
        $condition = (string) ($condition ?? 'all');
        $category = (string) ($category ?? 'all');
        $availability = (string) ($availability ?? 'all');

        $query = book::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('author', 'like', '%' . $search . '%')
                    ->orWhere('isbn', 'like', '%' . $search . '%')
                    ->orWhere('publisher', 'like', '%' . $search . '%');
            });
        }

        if ($condition !== 'all') {
            $query->where('condition', $condition);
        }

        if ($category !== 'all') {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        if ($availability !== 'all') {
            switch ($availability) {
                case 'out-of-stock':
                    $query->where('available_copies', 0);
                    break;
                case 'low-stock':
                    $query->whereBetween('available_copies', [1, 5]);
                    break;
                case 'in-stock':
                    $query->where('available_copies', '>=', 6);
                    break;
            }
        }

        return $query;
    }

    private function calculateBookStats($query): array
    {
        $books = $query->with('category')->get();

        $totalCopies = 0;
        $availableCopies = 0;
        $topCategories = [];
        $conditionBreakdown = [];

        foreach ($books as $book) {
            $totalCopies += $book->total_copies ?? 0;
            $availableCopies += $book->available_copies ?? 0;

            if (!empty($book->condition)) {
                $conditionBreakdown[$book->condition] = ($conditionBreakdown[$book->condition] ?? 0) + 1;
            }

            if ($book->category) {
                $categoryName = $book->category->name;
                $topCategories[$categoryName] = ($topCategories[$categoryName] ?? 0) + 1;
            }
        }

        arsort($topCategories);

        return [
            'totalBooks' => $books->count(),
            'totalCopies' => $totalCopies,
            'availableCopies' => $availableCopies,
            'borrowedCopies' => $totalCopies - $availableCopies,
            'topCategories' => $topCategories,
            'conditionBreakdown' => $conditionBreakdown,
        ];
    }
}
