<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StudentBookRequestStoreRequest;
use App\Http\Resources\BookRequestResource;
use App\Models\Book;
use App\Models\BookRequest;
use App\Models\IssuedBook;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentBookRequestController extends Controller
{
    use ResolvesApiUsers;

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $requests = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->where('student_id', $student->id)
            ->latest('request_date')
            ->paginate($this->perPage($request));

        return BookRequestResource::collection($requests);
    }

    public function store(StudentBookRequestStoreRequest $request, NotificationService $notifications): JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $book = Book::query()->with('category')->findOrFail($request->integer('book_id'));

        if ((int) $book->available_copies <= 0) {
            return $this->validationError('book_id', 'This book is currently unavailable.');
        }

        $alreadyIssued = IssuedBook::query()
            ->where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->whereNull('return_date')
            ->exists();

        if ($alreadyIssued) {
            return $this->validationError('book_id', 'You already have this book issued.');
        }

        $duplicateRequest = BookRequest::query()
            ->where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($duplicateRequest) {
            return $this->validationError('book_id', 'You already have an active request for this book.');
        }

        $bookRequest = BookRequest::create([
            'student_id' => $student->id,
            'book_id' => $book->id,
            'request_date' => now(),
            'status' => 'pending',
        ])->load(['student.user', 'student.department', 'book.category']);

        $notifications->notifyBookRequestCreated($bookRequest, (string) $request->input('remarks', ''));

        return response()->json([
            'message' => 'Book request submitted successfully.',
            'data' => new BookRequestResource($bookRequest),
        ], 201);
    }

    public function show(Request $request, int $id): BookRequestResource|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $bookRequest = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->findOrFail($id);

        if ((int) $bookRequest->student_id !== (int) $student->id) {
            return $this->forbid();
        }

        return new BookRequestResource($bookRequest);
    }

    public function summary(Request $request): JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $baseQuery = BookRequest::query()->where('student_id', $student->id);

        return response()->json([
            'total_requests' => (int) (clone $baseQuery)->count(),
            'pending_requests' => (int) (clone $baseQuery)->where('status', 'pending')->count(),
            'approved_requests' => (int) (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected_requests' => (int) (clone $baseQuery)->where('status', 'rejected')->count(),
            'cancelled_requests' => (int) (clone $baseQuery)->where('status', 'cancelled')->count(),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $bookRequest = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->findOrFail($id);

        if ((int) $bookRequest->student_id !== (int) $student->id) {
            return $this->forbid();
        }

        if ($bookRequest->status !== 'pending') {
            return $this->validationError('request', 'Only pending requests can be cancelled.');
        }

        $bookRequest->update([
            'status' => 'cancelled',
            'processed_by' => (string) ($request->user()->name ?? 'Student'),
            'processed_date' => now(),
        ]);

        return response()->json([
            'message' => 'Book request cancelled successfully.',
            'data' => new BookRequestResource($bookRequest->fresh(['student.user', 'student.department', 'book.category'])),
        ]);
    }

    protected function validationError(string $field, string $message): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => [
                $field => [$message],
            ],
        ], 422);
    }

}
