<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SearchRequest;
use App\Http\Resources\BookResource;
use App\Models\book as Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $books = $this->applyCatalogFilters(Book::query()->with('category'), $request)
            ->paginate($this->perPage($request))
            ->appends($request->query());

        return BookResource::collection($books);
    }

    public function show(int $id): BookResource
    {
        return new BookResource(
            Book::query()->with('category')->findOrFail($id)
        );
    }

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = $request->string('q')->toString();

        $books = $this->applyCatalogFilters(
            Book::query()
                ->with('category')
                ->where(function ($builder) use ($query) {
                    $builder
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('author', 'like', "%{$query}%")
                        ->orWhere('publisher', 'like', "%{$query}%")
                        ->orWhere('isbn', 'like', "%{$query}%")
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$query}%"));
                }),
            $request
        )
            ->paginate($this->perPage($request))
            ->appends($request->query());

        return BookResource::collection($books);
    }

    public function category(string $category, Request $request): AnonymousResourceCollection
    {
        $books = Book::query()
            ->with('category')
            ->whereHas('category', function ($builder) use ($category) {
                $builder->when(
                    is_numeric($category),
                    fn ($q) => $q->where('id', (int) $category),
                    fn ($q) => $q->where('name', $category)
                );
            })
            ->orderBy('title')
            ->paginate($this->perPage($request));

        return BookResource::collection($books);
    }

    public function available(Request $request): AnonymousResourceCollection
    {
        $books = Book::query()
            ->with('category')
            ->where('available_copies', '>', 0)
            ->orderBy('title')
            ->paginate($this->perPage($request));

        return BookResource::collection($books);
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }

    private function applyCatalogFilters($query, Request $request)
    {
        $category = trim($request->string('category')->toString());
        if ($category !== '') {
            $query->whereHas('category', function ($builder) use ($category) {
                $builder->when(
                    is_numeric($category),
                    fn ($q) => $q->where('id', (int) $category),
                    fn ($q) => $q->where('name', $category)
                );
            });
        }

        $availability = strtolower(trim($request->string('availability')->toString()));
        if ($availability === 'available') {
            $query->where('available_copies', '>', 0);
        } elseif ($availability === 'unavailable') {
            $query->where('available_copies', '<=', 0);
        }

        $condition = trim($request->string('condition')->toString());
        if ($condition !== '') {
            $query->where('condition', $condition);
        }

        return match ($request->string('sort')->toString()) {
            'title_desc' => $query->orderByDesc('title'),
            'author_asc' => $query->orderBy('author')->orderBy('title'),
            'available_desc' => $query->orderByDesc('available_copies')->orderBy('title'),
            'recently_added' => $query->orderByDesc('created_at')->orderByDesc('id'),
            default => $query->orderBy('title'),
        };
    }
}
