<?php

namespace App\Console\Commands;

use App\Models\Fine;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendFineReminders extends Command
{
    protected $signature = 'notifications:fine-reminders';
    protected $description = 'Send reminders to students for unpaid fines';

    public function handle()
    {
        $this->info('Sending fine payment reminders...');

        // Find unpaid fines
        $unpaidFines = Fine::where('status', 'unpaid')
            ->with(['student.user', 'issuedBook.book'])
            ->get();

        $count = 0;
        foreach ($unpaidFines as $fine) {
            if (!$fine->student || !$fine->student->user) {
                continue;
            }

            // Check if we already sent a reminder today
            $existingReminder = Notification::where('user_id', $fine->student->user->id)
                ->where('type', 'fine.reminder')
                ->whereDate('created_at', Carbon::today())
                ->where('related_model', 'Fine')
                ->where('related_id', $fine->id)
                ->exists();

            if ($existingReminder) {
                continue;
            }

            $bookTitle = $fine->issuedBook?->book?->title ?? 'Unknown Book';

            Notification::notify(
                user: $fine->student->user,
                type: 'fine.reminder',
                title: 'Fine Payment Reminder',
                message: "You have an unpaid fine of ₹{$fine->amount} for '{$bookTitle}'. Please pay it as soon as possible.",
                data: [
                    'fine_id' => $fine->id,
                    'amount' => $fine->amount,
                    'book_title' => $bookTitle,
                ],
                relatedModel: 'Fine',
                relatedId: $fine->id
            );

            $count++;
        }

        $this->info("Sent {$count} fine payment reminders.");
        return 0;
    }
}
