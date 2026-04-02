<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UiShowcaseController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('access-admin');

        $palettes = [
            'blue' => ['name' => 'Blue', 'primary' => '#2563eb', 'light' => '#3b82f6', 'dark' => '#1d4ed8', 'darkMode' => '#60a5fa'],
            'indigo' => ['name' => 'Indigo', 'primary' => '#4f46e5', 'light' => '#6366f1', 'dark' => '#4338ca', 'darkMode' => '#818cf8'],
            'purple' => ['name' => 'Purple', 'primary' => '#7c3aed', 'light' => '#8b5cf6', 'dark' => '#6d28d9', 'darkMode' => '#a78bfa'],
            'emerald' => ['name' => 'Emerald', 'primary' => '#059669', 'light' => '#10b981', 'dark' => '#047857', 'darkMode' => '#34d399'],
            'rose' => ['name' => 'Rose', 'primary' => '#e11d48', 'light' => '#f43f5e', 'dark' => '#be123c', 'darkMode' => '#fb7185'],
            'orange' => ['name' => 'Orange', 'primary' => '#ea580c', 'light' => '#f97316', 'dark' => '#c2410c', 'darkMode' => '#fb923c'],
        ];

        $sampleData = [
            ['id' => 1, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'status' => 'available', 'category' => 'Fiction'],
            ['id' => 2, 'title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'status' => 'issued', 'category' => 'Fiction'],
            ['id' => 3, 'title' => '1984', 'author' => 'George Orwell', 'status' => 'reserved', 'category' => 'Science Fiction'],
            ['id' => 4, 'title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'status' => 'available', 'category' => 'Romance'],
            ['id' => 5, 'title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'status' => 'issued', 'category' => 'Fiction'],
        ];

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 5;
        $paginationDataset = $this->buildPaginationDataset(collect($sampleData));

        $paginator = new LengthAwarePaginator(
            $paginationDataset->forPage($page, $perPage)->values(),
            $paginationDataset->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('Admin.ui-showcase.index', [
            'palettes' => $palettes,
            'sampleData' => $sampleData,
            'paginator' => $paginator,
        ]);
    }

    protected function buildPaginationDataset(Collection $sampleData): Collection
    {
        return collect(range(1, 30))->map(function (int $index) use ($sampleData) {
            $record = $sampleData->values()->get(($index - 1) % $sampleData->count());

            return [
                'id' => $index,
                'title' => $record['title'],
                'author' => $record['author'],
                'status' => $record['status'],
                'category' => $record['category'],
            ];
        });
    }
}
