<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\book;
use App\Models\category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SearchBookController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('access-student');

        $query = trim((string) $request->input('q', ''));
        $categoryId = $request->input('category');
        $availability = $request->input('availability');
        $condition = $request->input('condition');
        $sort = $request->input('sort', 'title_asc');

        $booksQuery = book::query()->with('category');
        if ($query) {
            $booksQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%$query%")
                    ->orWhere('author', 'like', "%$query%")
                    ->orWhere('isbn', 'like', "%$query%")
                    ->orWhere('publisher', 'like', "%$query%")
                    ->orWhere('shelf_no', 'like', "%$query%");
            });
        }
        if ($categoryId) {
            $booksQuery->where('category_id', $categoryId);
        }

        if ($availability === 'available') {
            $booksQuery->where('available_copies', '>', 0);
        } elseif ($availability === 'unavailable') {
            $booksQuery->where('available_copies', '<=', 0);
        }

        if (in_array($condition, ['new', 'good', 'damaged'], true)) {
            $booksQuery->where('condition', $condition);
        }

        $this->applySort($booksQuery, $sort);

        $books = $booksQuery->get();

        $categories = category::orderBy('name')->get();

        // If AJAX request, return JSON
        if ($request->ajax()) {
            $booksArray = $books->map(function ($book) {
                return [
                    'id' => (int) $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'publisher' => $book->publisher,
                    'isbn' => $book->isbn,
                    'total_copies' => $book->total_copies,
                    'available_copies' => $book->available_copies,
                    'condition' => $book->condition,
                    'description' => $book->description,
                    'display_description' => $this->resolveCardDescription($book->description),
                    'cover_image' => $book->cover_image,
                    'shelf_no' => $book->shelf_no,
                    'status' => $book->status,
                    'category' => $book->category ? ['id' => $book->category->id, 'name' => $book->category->name] : null,
                ];
            })->values()->all();
            
            return response()->json([
                'books' => $booksArray,
                'count' => count($booksArray),
            ]);
        }

        return view('Student.SearchBook', [
            'books' => $books,
            'categories' => $categories,
            'search' => $query,
            'selectedCategory' => $categoryId,
            'selectedAvailability' => $availability,
            'selectedCondition' => $condition,
            'selectedSort' => $sort,
        ]);
    }

    protected function applySort($booksQuery, ?string $sort): void
    {
        switch ($sort) {
            case 'title_desc':
                $booksQuery->orderBy('title', 'desc');
                break;
            case 'author_asc':
                $booksQuery->orderBy('author')->orderBy('title');
                break;
            case 'copies_desc':
                $booksQuery->orderBy('available_copies', 'desc')->orderBy('title');
                break;
            case 'recent':
                $booksQuery->latest();
                break;
            default:
                $booksQuery->orderBy('title');
                break;
        }
    }

    protected function resolveCardDescription(?string $description): ?string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', (string) $description));

        if ($normalized === '') {
            return null;
        }

        if (in_array(Str::lower($normalized), [
            'comprehensive learning resource',
            'a comprehensive learning resource',
        ], true)) {
            return null;
        }

        return Str::limit($normalized, 120);
    }

    public function requestBook(Request $request)
    {
        Gate::authorize('access-student');

        $bookId = $request->input('book_id');
        
        // Debug: Log the book ID being searched
        \Log::info('Book request received for book_id: ' . $bookId);
        
        // Check if book exists
        $book = \App\Models\book::find($bookId);
        if (!$book) {
            \Log::error('Book not found with ID: ' . $bookId);
            return response()->json(['success' => false, 'message' => 'Book not found. ID: ' . $bookId], 404);
        }

        // Get authenticated student
        $user = \Illuminate\Support\Facades\Auth::user();
        $student = $user ? $user->student : null;
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 400);
        }

        // Check if already requested and not yet returned
        $existingRequest = \App\Models\BookRequest::where('student_id', $student->id)
            ->where('book_id', $bookId)
            ->whereIn('status', ['pending', 'approved', 'issued'])
            ->first();

        if ($existingRequest) {
            return response()->json(['success' => false, 'message' => 'You have already requested this book. Please wait until the book is returned before requesting again.'], 400);
        }

        // Create book request
        try {
            $bookRequest = \App\Models\BookRequest::create([
                'student_id' => $student->id,
                'book_id' => $bookId,
                'request_date' => now(),
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Book request submitted successfully!',
                'request' => $bookRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error submitting request: ' . $e->getMessage()], 500);
        }
    }
}
