<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\book;
use App\Models\category;
use App\Models\Notification;
use App\Models\User;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookManagementController extends Controller
{
    public function index()
    {
        Gate::authorize('access-staff');
        $categories = category::orderBy('name')->get();
        return view('Staff.BookManagement', compact('categories'));
    }

    public function getBooksData(Request $request)
    {
        Gate::authorize('access-staff');

        $search = $request->get('search', '');
        $condition = $request->get('condition', 'all');
        $category = $request->get('category', 'all');
        $availability = $request->get('availability', 'all');
        $sort = $request->get('sort', 'title-asc');
        $page = $request->get('page', 1);
        $perPage = 7;

        $query = book::query();

        // Search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('author', 'like', '%' . $search . '%')
                  ->orWhere('isbn', 'like', '%' . $search . '%')
                  ->orWhere('publisher', 'like', '%' . $search . '%');
            });
        }

        // Condition filter
        if ($condition !== 'all') {
            $query->where('condition', $condition);
        }

        // Category filter
        if ($category !== 'all') {
            $query->whereHas('category', function($q) use ($category) {
                $q->where('name', $category);
            });
        }

        // Availability filter
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

        // Sorting
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

        $books = $query->paginate($perPage, ['*'], 'page', $page);
        $books->load('category');

        $tableRows = '';
        foreach ($books->items() as $book) {
            $conditionClass = $book->condition === 'new' ? 'condition-new' : ($book->condition === 'damaged' ? 'condition-damaged' : 'condition-good');
            $conditionIcon = $book->condition === 'new' ? 'fa-star' : ($book->condition === 'damaged' ? 'fa-exclamation-triangle' : 'fa-check-circle');
            $conditionText = ucfirst($book->condition);

            $tableRows .= '<tr data-book-id="' . $book->id . '" data-category="' . ($book->category->id ?? '') . '" data-category-id="' . ($book->category->id ?? '') . '" data-category-name="' . htmlspecialchars($book->category->name ?? 'N/A') . '" data-condition="' . $book->condition . '" data-cover="' . htmlspecialchars($book->cover_image ?? '') . '" data-description="' . htmlspecialchars($book->description ?? '') . '" data-publisher="' . htmlspecialchars($book->publisher ?? '') . '" data-isbn="' . htmlspecialchars($book->isbn ?? '') . '" data-shelf="' . htmlspecialchars($book->shelf_no ?? '') . '" data-total-copies="' . htmlspecialchars($book->total_copies ?? 0) . '" data-available-copies="' . htmlspecialchars($book->available_copies ?? 0) . '">';
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

        $paginationHtml = $books->links()->toHtml();
        $stats = $this->getBookStats();

        return response()->json([
            'success' => true,
            'tableRows' => $tableRows,
            'pagination' => $paginationHtml,
            'stats' => $stats,
            'total' => $books->total(),
            'current_page' => $books->currentPage(),
            'last_page' => $books->lastPage(),
        ]);
    }

    public function getBookStats(Request $request = null)
    {
        Gate::authorize('access-staff');

        $totalBooks = book::count();
        $totalCopies = book::sum('total_copies') ?? 0;
        $availableCopies = book::sum('available_copies') ?? 0;
        $borrowedCopies = $totalCopies - $availableCopies;

        $topCategories = book::select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->orderByDesc('count')
            ->get();

        $categoriesWithNames = [];
        foreach ($topCategories as $item) {
            if ($item->category) {
                $categoriesWithNames[$item->category->name] = $item->count;
            }
        }

        $conditionBreakdown = book::select('condition', DB::raw('count(*) as count'))
            ->groupBy('condition')
            ->pluck('count', 'condition')
            ->toArray();

        $stats = [
            'totalBooks' => $totalBooks,
            'totalCopies' => $totalCopies,
            'availableCopies' => $availableCopies,
            'borrowedCopies' => $borrowedCopies,
            'topCategories' => $categoriesWithNames,
            'conditionBreakdown' => $conditionBreakdown,
        ];

        if ($request && $request->expectsJson()) {
            return response()->json($stats);
        }

        return $stats;
    }

    public function store(Request $request)
    {
        Gate::authorize('access-staff');

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'required|string|unique:books',
                'publisher' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer|exists:categories,id',
                'new_category' => 'nullable|string|max:255',
                'total_copies' => 'required|integer|min:1',
                'available_copies' => 'required|integer|min:0',
                'condition' => 'required|in:new,good,damaged',
                'shelf_no' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $categoryId = !empty($validated['category_id']) ? (int)$validated['category_id'] : null;
            $newCategoryName = !empty($validated['new_category']) ? trim($validated['new_category']) : null;

            if (!$categoryId && !$newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select an existing category or create a new one',
                ], 422);
            }

            if ($categoryId && $newCategoryName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please choose either an existing category OR create a new one, not both',
                ], 422);
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

            // Handle optional cover upload
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

            // Notify admin if low stock
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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book created successfully',
                    'book' => $book,
                ]);
            }

            return redirect()->route('staff.books.index')->with('success', 'Book created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating book: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        Gate::authorize('access-staff');

        $book = book::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $id,
            'publisher' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'total_copies' => 'required|integer|min:1',
            'available_copies' => 'required|integer|min:0',
            'condition' => 'required|in:new,good,damaged',
            'shelf_no' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $oldCondition = $book->condition;
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Book updated successfully',
                'book' => $book,
            ]);
        }

        return redirect()->route('staff.books.index')->with('success', 'Book updated successfully');
    }

    public function destroy(string $id)
    {
        Gate::authorize('access-staff');

        $book = book::findOrFail($id);
        $bookTitle = $book->title;
        $category = $book->category;

        $book->delete();

        ActivityLogger::logActivity(
            'book_deleted',
            'Deleted Book: ' . $bookTitle . ' (Category: ' . ($category->name ?? 'N/A') . ')',
            'book',
            'book',
            $id
        );

        $request = request();
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Book deleted successfully',
            ]);
        }

        return redirect()->route('staff.books.index')->with('success', 'Book deleted successfully');
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
}
