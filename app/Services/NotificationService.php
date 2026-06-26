<?php

namespace App\Services;

use App\Jobs\SendBookIssuedEmail;
use App\Jobs\SendBookReturnedEmail;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function create(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $relatedModel = null,
        ?int $relatedId = null
    ): ?Notification {
        try {
            return Notification::notify($user, $type, $title, $message, $data, $relatedModel, $relatedId);
        } catch (Throwable $exception) {
            Log::warning('Unable to create notification: ' . $exception->getMessage(), [
                'user_id' => $user->id,
                'type' => $type,
                'related_model' => $relatedModel,
                'related_id' => $relatedId,
            ]);

            return null;
        }
    }

    public function notifyBookRequestCreated(BookRequest $bookRequest, string $remarks = '', bool $includeAdmins = true): void
    {
        $bookRequest->loadMissing(['student.user', 'book']);
        $studentName = $bookRequest->student?->user?->name ?? 'A Student';

        User::query()
            ->where('role', 'staff')
            ->when($includeAdmins, fn ($query) => $query->orWhere('role', 'admin'))
            ->get()
            ->each(function (User $user) use ($bookRequest, $studentName, $remarks): void {
                $this->create(
                    user: $user,
                    type: 'student.book_request',
                    title: 'New Book Request from Student',
                    message: "{$studentName} requested book '{$bookRequest->book?->title}'",
                    data: [
                        'request_id' => $bookRequest->id,
                        'student_id' => $bookRequest->student_id,
                        'student_name' => $studentName,
                        'book_id' => $bookRequest->book_id,
                        'book_title' => $bookRequest->book?->title,
                        'isbn' => $bookRequest->book?->isbn,
                        'remarks' => $remarks,
                    ],
                    relatedModel: 'BookRequest',
                    relatedId: $bookRequest->id
                );
            });
    }

    public function notifyBookRequestStatusChanged(BookRequest $bookRequest, string $status): void
    {
        $bookRequest->loadMissing(['student.user', 'book']);

        if (! $bookRequest->student?->user) {
            return;
        }

        $notificationType = $status === 'approved' ? 'request.approved' : 'request.rejected';
        $title = $status === 'approved' ? 'Request Approved' : 'Request Rejected';
        $message = $status === 'approved'
            ? "Your request for '{$bookRequest->book?->title}' has been approved!"
            : "Your request for '{$bookRequest->book?->title}' has been rejected.";

        $this->create(
            user: $bookRequest->student->user,
            type: $notificationType,
            title: $title,
            message: $message,
            data: [
                'request_id' => $bookRequest->id,
                'book_id' => $bookRequest->book_id,
                'status' => $status,
                'book_title' => $bookRequest->book?->title,
            ],
            relatedModel: 'BookRequest',
            relatedId: $bookRequest->id
        );
    }

    public function notifyBookRequestProcessedAdmin(BookRequest $bookRequest, string $status): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        if (! $admin) {
            return;
        }

        $bookRequest->loadMissing(['student.user', 'book']);

        $this->create(
            user: $admin,
            type: 'request.pending',
            title: 'Book Request Processed',
            message: "Request from {$bookRequest->student?->user?->name} for '{$bookRequest->book?->title}' has been {$status}",
            data: [
                'request_id' => $bookRequest->id,
                'status' => $status,
                'student_id' => $bookRequest->student_id,
                'book_id' => $bookRequest->book_id,
            ],
            relatedModel: 'BookRequest',
            relatedId: $bookRequest->id
        );
    }

    public function notifyBookIssued(IssuedBook $issuedBook): void
    {
        $issuedBook->loadMissing(['student.user', 'book']);
        $student = $issuedBook->student;
        $book = $issuedBook->book;

        if (! $student?->user || ! $book) {
            return;
        }

        $this->create(
            user: $student->user,
            type: 'book.issued',
            title: 'Book Issued Successfully',
            message: "You have been issued '{$book->title}' by {$book->author}.",
            data: [
                'book_id' => $book->id,
                'issued_book_id' => $issuedBook->id,
                'issue_date' => optional($issuedBook->issue_date)->toDateString(),
                'due_date' => optional($issuedBook->due_date)->toDateString(),
            ],
            relatedModel: 'IssuedBook',
            relatedId: $issuedBook->id
        );

        if ($student->user->email) {
            try {
                SendBookIssuedEmail::dispatch(
                    $student->user->email,
                    $student->user->name,
                    $book->title,
                    $book->author ?? 'Unknown',
                    optional($issuedBook->issue_date)->format('Y-m-d'),
                    optional($issuedBook->due_date)->format('Y-m-d')
                );
            } catch (Throwable $exception) {
                Log::warning('Unable to queue book issued email: ' . $exception->getMessage(), [
                    'issued_book_id' => $issuedBook->id,
                ]);
            }
        }
    }

    public function notifyBookReturned(IssuedBook $issuedBook, string $condition, float $fineAmount): void
    {
        $issuedBook->loadMissing(['student.user', 'book']);
        $student = $issuedBook->student;

        if (! $student?->user) {
            return;
        }

        $title = $fineAmount > 0 ? 'Book Returned with Fine' : 'Book Returned Successfully';
        $message = $fineAmount > 0
            ? "Your return of '{$issuedBook->book?->title}' has been processed. Fine amount: Rs. {$fineAmount}."
            : "Your return of '{$issuedBook->book?->title}' has been accepted.";

        $this->create(
            user: $student->user,
            type: $fineAmount > 0 ? 'fine.created' : 'book.returned',
            title: $title,
            message: $message,
            data: [
                'book_id' => $issuedBook->book_id,
                'issued_book_id' => $issuedBook->id,
                'condition' => $condition,
                'fine_amount' => $fineAmount,
            ],
            relatedModel: 'IssuedBook',
            relatedId: $issuedBook->id
        );

        if ($student->user->email) {
            try {
                SendBookReturnedEmail::dispatch(
                    $student->user->email,
                    $student->user->name,
                    $issuedBook->book?->title ?? 'Book',
                    $condition,
                    $fineAmount
                );
            } catch (Throwable $exception) {
                Log::warning('Unable to queue book returned email: ' . $exception->getMessage(), [
                    'issued_book_id' => $issuedBook->id,
                ]);
            }
        }
    }

    public function notifyFinePaid(Fine $fine): void
    {
        $fine->loadMissing(['student.user']);
        $student = $fine->student;

        if (! $student?->user) {
            return;
        }

        $this->create(
            user: $student->user,
            type: 'payment.confirmed',
            title: 'Fine Payment Received',
            message: "Your fine payment of Rs. {$fine->amount} has been received and marked as paid.",
            data: [
                'fine_id' => $fine->id,
                'amount' => $fine->amount,
                'student_id' => $student->id,
            ],
            relatedModel: 'Fine',
            relatedId: $fine->id
        );
    }

    public function notifyFineWaived(Fine $fine, string $reason): void
    {
        $fine->loadMissing(['student.user']);
        $student = $fine->student;

        if (! $student?->user) {
            return;
        }

        $this->create(
            user: $student->user,
            type: 'fine.reminder',
            title: 'Fine Waived',
            message: "Your fine of Rs. {$fine->amount} has been waived. Reason: {$reason}",
            data: [
                'fine_id' => $fine->id,
                'amount' => $fine->amount,
                'reason' => $reason,
            ],
            relatedModel: 'Fine',
            relatedId: $fine->id
        );
    }

    public function notifyProfileUpdated(User $user, array $profileChanges, Request $request): void
    {
        if (empty($profileChanges['messages'])) {
            return;
        }

        $this->create(
            user: $user,
            type: 'account.profile_updated',
            title: 'Profile Information Updated',
            message: 'Your profile information was updated: ' . $profileChanges['summary'],
            data: [
                'ip' => $request->ip(),
                'timestamp' => now(),
                'changes' => $profileChanges['changes'] ?? [],
                'changed_fields' => $profileChanges['changed_fields'] ?? [],
            ],
            relatedModel: 'User',
            relatedId: $user->id
        );
    }

    public function notifyPasswordChanged(User $user, Request $request): void
    {
        $timestamp = now();
        $ipAddress = $request->ip();

        $this->create(
            user: $user,
            type: 'account.password_changed',
            title: 'Password Changed Successfully',
            message: "Your password was changed successfully on {$timestamp->format('M d, Y')} at {$timestamp->format('h:i A')} from IP {$ipAddress}",
            data: [
                'ip' => $ipAddress,
                'timestamp' => $timestamp,
                'date_formatted' => $timestamp->format('M d, Y h:i A'),
                'user_agent' => $request->header('User-Agent'),
            ],
            relatedModel: 'User',
            relatedId: $user->id
        );
    }

    public function notifyPhotoUpdated(User $user, Request $request): void
    {
        $this->create(
            user: $user,
            type: 'account.profile_updated',
            title: 'Profile Photo Updated',
            message: 'Your profile photo was updated successfully.',
            data: [
                'ip' => $request->ip(),
                'timestamp' => now(),
                'changed_fields' => ['profile_photo'],
            ],
            relatedModel: 'User',
            relatedId: $user->id
        );
    }

    public function notifyPhotoRemoved(User $user, Request $request): void
    {
        $this->create(
            user: $user,
            type: 'account.profile_updated',
            title: 'Profile Photo Removed',
            message: 'Your profile photo was removed successfully.',
            data: [
                'ip' => $request->ip(),
                'timestamp' => now(),
                'changed_fields' => ['profile_photo'],
            ],
            relatedModel: 'User',
            relatedId: $user->id
        );
    }

    public function notifyAccountDeactivated(User $user, Request $request): void
    {
        $this->create(
            user: $user,
            type: 'account.status_changed',
            title: 'Account Status Changed',
            message: 'Your account has been deactivated.',
            data: [
                'status' => 'inactive',
                'changed_by' => $user->name,
                'ip' => $request->ip(),
                'timestamp' => now(),
            ],
            relatedModel: $user->role === 'staff' ? 'Staff' : 'Student',
            relatedId: $user->role === 'staff' ? $user->staff?->id : $user->student?->id
        );
    }
}
