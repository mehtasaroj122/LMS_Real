<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequestManagement\ListBookRequestsRequest;
use App\Http\Requests\BookRequestManagement\StoreBookRequestRequest;
use App\Http\Requests\BookRequestManagement\UpdateBookRequestStatusRequest;
use App\Models\BookRequest;
use App\Services\BookRequestManagement\BookRequestManagementActionService;
use App\Services\BookRequestManagement\BookRequestManagementDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookRequestController extends Controller
{
    public function index(BookRequestManagementDataService $dataService)
    {
        Gate::authorize('access-staff');

        $referenceData = $dataService->getReferenceData();

        return view('Staff.BookRequest', [
            'students' => $referenceData['students'],
            'books' => $referenceData['books'],
        ]);
    }

    public function getRequestsData(
        ListBookRequestsRequest $request,
        BookRequestManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        return response()->json([
            'success' => true,
            ...$dataService->getListingData($request->validated()),
        ]);
    }

    public function getRequestStats(
        ListBookRequestsRequest $request,
        BookRequestManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        $stats = $dataService->getStats($request->validated());

        if ($request->expectsJson()) {
            return response()->json($stats);
        }

        return $stats;
    }

    public function getNextPending(Request $request, BookRequestManagementDataService $dataService)
    {
        Gate::authorize('access-staff');

        $excludeIds = collect(explode(',', (string) $request->get('exclude', '')))
            ->map(static fn (string $value) => (int) $value)
            ->filter(static fn (int $value) => $value > 0)
            ->values()
            ->all();

        $nextRequest = $dataService->getNextPending($excludeIds);

        if (!$nextRequest) {
            return response()->json([
                'success' => false,
                'message' => 'No more pending requests',
            ]);
        }

        return response()->json([
            'success' => true,
            'request' => $nextRequest,
        ]);
    }

    public function store(
        StoreBookRequestRequest $request,
        BookRequestManagementActionService $actionService,
        BookRequestManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        $bookRequest = $actionService->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Request created successfully.',
                'request' => $dataService->serializeRequest($bookRequest),
            ]);
        }

        return redirect()
            ->route('staff.book-requests.index')
            ->with('success', 'Request created successfully.');
    }

    public function update(
        UpdateBookRequestStatusRequest $request,
        string $id,
        BookRequestManagementActionService $actionService,
        BookRequestManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        $bookRequest = BookRequest::with(['student.user', 'book'])->findOrFail($id);
        $status = $request->status();

        $bookRequest = $actionService->updateStatus(
            $bookRequest,
            $status,
            (string) (auth()->user()?->name ?? 'System')
        );

        $message = $status === 'approved'
            ? 'Request approved successfully.'
            : 'Request rejected successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'request' => $dataService->serializeRequest($bookRequest),
            ]);
        }

        return redirect()
            ->route('staff.book-requests.index')
            ->with('success', $message);
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        Gate::authorize('access-staff');

        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->delete();

        $request = request();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Request deleted successfully.',
            ]);
        }

        return redirect()
            ->route('staff.book-requests.index')
            ->with('success', 'Request deleted successfully.');
    }
}
