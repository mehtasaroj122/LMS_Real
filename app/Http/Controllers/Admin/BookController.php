<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookStoreRequest;
use App\Models\book;
use App\Models\category;
use App\Models\Notification;
use App\Models\User;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('access-admin');
        $categories = category::orderBy('name')->get();
        ['search' => $search, 'condition' => $condition, 'category' => $selectedCategory, 'availability' => $availability, 'sort' => $sort, 'page' => $page] = $this->normalizeBookListFilters($request);

        $initialBooksQuery = $this->buildFilteredBooksQuery($search, $condition, $selectedCategory, $availability);
        $this->applyBookSorting($initialBooksQuery, $sort);

        $initialBooks = $initialBooksQuery
            ->with('category')
            ->paginate(15, ['*'], 'page', $page);
        $initialStats = $this->calculateBookStats(
            $this->buildFilteredBooksQuery($search, $condition, $selectedCategory, $availability)
        );

        return view('admin.BookManagement', compact(
            'categories',
            'initialBooks',
            'initialStats',
            'search',
            'condition',
            'selectedCategory',
            'availability',
            'sort'
        ));
    }

    /**
     * Get books data with search, filter and pagination for AJAX requests
     */
    public function getBooksData(Request $request)
    {
        Gate::authorize('access-admin');

        ['search' => $search, 'condition' => $condition, 'category' => $category, 'availability' => $availability, 'sort' => $sort, 'page' => $page] = $this->normalizeBookListFilters($request);
        $perPage = 15; // You can adjust this as needed

        $query = $this->buildFilteredBooksQuery($search, $condition, $category, $availability);
        $this->applyBookSorting($query, $sort);

        // Paginate
        $books = $query->paginate($perPage, ['*'], 'page', $page);

        // Load relationships after pagination
        $books->load('category');

        // Generate table rows HTML
        $tableRows = '';
        foreach ($books->items() as $book) {
            $conditionClass = $book->condition === 'new' ? 'condition-new' : ($book->condition === 'damaged' ? 'condition-damaged' : 'condition-good');
            $conditionIcon = $book->condition === 'new' ? 'fa-star' : ($book->condition === 'damaged' ? 'fa-exclamation-triangle' : 'fa-check-circle');
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
            
            // Add cover image
            if ($book->cover_image) {
                $imageUrl = str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset('storage/' . $book->cover_image);
                $tableRows .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($book->title) . '" style="width: 40px; height: 50px; border-radius: 4px; object-fit: cover; border: 1px solid #e5e7eb;">';
            } else {
                $firstLetter = strtoupper(substr($book->title, 0, 1));
                $tableRows .= '<div style="width: 40px; height: 50px; min-width: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #ffffff; font-weight: 700; font-size: 20px; flex-shrink: 0;">';
                $tableRows .= $firstLetter;
                $tableRows .= '</div>';
            }
            
            $tableRows .= '<div>';
            $tableRows .= '<strong style="font-size: 13px;">' . htmlspecialchars($book->title) . '</strong>';
            $tableRows .= '<div style="font-size: 10px; color: #9ca3af; margin-top: 1px;">' . htmlspecialchars($book->publisher ?? 'Unknown') . '</div>';
            $tableRows .= '</div>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '<td>' . htmlspecialchars($book->author) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($book->category->name ?? 'N/A') . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($book->shelf_no ?? 'N/A') . '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<div class="copy-count">';
            $tableRows .= '<span class="copy-total">' . $book->total_copies . '</span>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<div class="copy-count">';
            $tableRows .= '<span class="copy-available">' . $book->available_copies . '</span>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<span class="condition-badge ' . $conditionClass . '">';
            $tableRows .= '<i class="fas ' . $conditionIcon . '"></i>';
            $tableRows .= $conditionText;
            $tableRows .= '</span>';
            $tableRows .= '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<div class="action-buttons">';
            $tableRows .= '<button class="action-btn view" title="View Details"><i class="fas fa-eye"></i></button>';
            $tableRows .= '<button class="action-btn edit" title="Edit Book"><i class="fas fa-edit"></i></button>';
            $tableRows .= '<button class="action-btn delete" title="Delete Book"><i class="fas fa-trash-alt"></i></button>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '</tr>';
        }

        // Generate pagination HTML
        $paginationHtml = $books->links()->toHtml();

        $filteredStats = $this->calculateBookStats(
            $this->buildFilteredBooksQuery($search, $condition, $category, $availability)
        );

        return response()->json([
            'success' => true,
            'tableRows' => $tableRows,
            'pagination' => $paginationHtml,
            'stats' => $filteredStats,
            'total' => $books->total(),
            'current_page' => $books->currentPage(),
            'last_page' => $books->lastPage(),
        ]);
    }

    /**
     * Get books statistics
     */
    public function getBookStats(Request $request = null)
    {
        Gate::authorize('access-admin');

        ['search' => $search, 'condition' => $condition, 'category' => $category, 'availability' => $availability] = $this->normalizeBookListFilters($request);

        $stats = $this->calculateBookStats(
            $this->buildFilteredBooksQuery(
                $search,
                $condition,
                $category,
                $availability
            )
        );

        // Return JSON if AJAX request
        if ($request && $request->expectsJson()) {
            return response()->json($stats);
        }

        // Otherwise return array (for internal use)
        return $stats;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookStoreRequest $request)
    {
        Gate::authorize('access-admin');

        try {
            $validated = $request->validated();

            // Validate that either category_id or new_category is provided (but not both)
            $categoryId = !empty($validated['category_id']) ? (int)$validated['category_id'] : null;
            $newCategoryName = !empty($validated['new_category']) ? trim($validated['new_category']) : null;
            
            if (!$categoryId && !$newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select an existing category or create a new one',
                    'errors' => ['category_id' => ['Please select an existing category or create a new one']],
                ], 422);
            }

            if ($categoryId && $newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please choose either an existing category OR create a new one, not both',
                    'errors' => ['new_category' => ['Please choose either existing OR new category, not both']],
                ], 422);
            }

            if (!$categoryId && $newCategoryName) {
                $categoryModel = category::firstOrCreate(['name' => $newCategoryName]);
                $categoryId = $categoryModel->id;
            }

            $validated['category_id'] = $categoryId;

            // Remove new_category from validated array as it's not a book column
            unset($validated['new_category']);

            // Ensure category_id is set
            if (empty($validated['category_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category ID is required',
                ], 422);
            }

            $book = book::create($validated);

            // Handle optional cover upload
            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('books/covers', 'public');
                $book->cover_image = $path;
                $book->save();
            }

            // Log activity
            $category = category::find($validated['category_id']);
            ActivityLogger::logActivity(
                'book_created',
                'Created Book: ' . $book->title . ' (Category: ' . ($category->name ?? 'N/A') . ')',
                'book',
                'book',
                $book->id,
                ['category_id' => $validated['category_id'], 'category_name' => $category->name ?? 'N/A']
            );

            // Notify all students of new book
            $students = User::where('role', 'student')->get();
            foreach ($students as $student) {
                Notification::notify(
                    user: $student,
                    type: 'book.added',
                    title: 'New Book Available',
                    message: "'{$book->title}' has been added to the library",
                    data: ['book_id' => $book->id, 'category_id' => $book->category_id, 'category_name' => $category->name ?? 'N/A'],
                    relatedModel: 'Book',
                    relatedId: $book->id
                );
            }

            // Check inventory and notify if low stock
            if ($book->available_copies < 5) {
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    Notification::notify(
                        user: $admin,
                        type: 'book.low_inventory',
                        title: 'Low Stock Alert',
                        message: "New book '{$book->title}' added with only {$book->available_copies} copy(ies)",
                        data: ['book_id' => $book->id, 'available_copies' => $book->available_copies, 'title' => $book->title],
                        relatedModel: 'Book',
                        relatedId: $book->id
                    );
                }
            }

            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book created successfully',
                    'book' => $book,
                ]);
            }

            return redirect()->route('admin.books.index')->with('success', 'Book created successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating book: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookStoreRequest $request, string $id)
    {
        Gate::authorize('access-admin');

        try {
            $book = book::findOrFail($id);

            $validated = $request->validated();
            $categoryId = !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
            $newCategoryName = !empty($validated['new_category']) ? trim($validated['new_category']) : null;

            if ($categoryId && $newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please choose either an existing category OR create a new one, not both',
                    'errors' => ['new_category' => ['Please choose either existing OR new category, not both']],
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

            // Handle cover replacement if a new file was uploaded
            if ($request->hasFile('cover_image')) {
                // Delete old cover if exists
                if (!empty($book->cover_image) && Storage::disk('public')->exists($book->cover_image)) {
                    Storage::disk('public')->delete($book->cover_image);
                }
                $path = $request->file('cover_image')->store('books/covers', 'public');
                $book->cover_image = $path;
                $book->save();
            }

            // Log activity
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

            // Check inventory and notify admin if low
            if ($book->available_copies < 5 && $oldCopies >= 5) {
                // Inventory crossed threshold - notify admin
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    Notification::notify(
                        user: $admin,
                        type: 'book.low_inventory',
                        title: 'Low Book Inventory Alert',
                        message: "{$book->title} now has only {$book->available_copies} copy(ies) remaining in stock",
                        data: ['book_id' => $book->id, 'available_copies' => $book->available_copies, 'title' => $book->title],
                        relatedModel: 'Book',
                        relatedId: $book->id
                    );
                }
            }

            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book updated successfully',
                    'book' => $book,
                ]);
            }

            return redirect()->route('admin.books.index')->with('success', 'Book updated successfully');
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('access-admin');

        $book = book::findOrFail($id);
        $bookTitle = $book->title;
        $category = $book->category;

        $book->delete();

        // Log activity
        ActivityLogger::logActivity(
            'book_deleted',
            'Deleted Book: ' . $bookTitle . ' (Category: ' . ($category->name ?? 'N/A') . ')',
            'book',
            'book',
            $id
        );

        // Check if this is an AJAX request
        $request = request();
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Book deleted successfully',
            ]);
        }

        return redirect()->route('admin.books.index')->with('success', 'Book deleted successfully');
    }

    /**
     * Create a new category
     */
    public function createCategory(Request $request)
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        try {
            $category = category::create($validated);

            // Log activity
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
        ];
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
