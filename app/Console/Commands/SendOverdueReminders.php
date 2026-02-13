<?php

namespace App\Console\Commands;

use App\Models\IssuedBook;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendOverdueReminders extends Command
{
    protected $signature = 'notifications:overdue-reminders';
    protected $description = 'Send reminders to students for overdue books';

    public function handle()
    {
        $this->info('Sending overdue book reminders...');

        // Find books that are overdue
        $overdueBooks = IssuedBook::whereNull('return_date')
            ->where('due_date', '<', Carbon::now())
            ->with(['student.user', 'book'])
            ->get();

        $count = 0;
        foreach ($overdueBooks as $issuedBook) {
            if (!$issuedBook->student || !$issuedBook->student->user) {
                continue;
            }

            // Check if we already sent a reminder today
            $existingReminder = Notification::where('user_id', $issuedBook->student->user->id)
                ->where('type', 'book.overdue')
                ->whereDate('created_at', Carbon::today())
                ->where('related_model', 'IssuedBook')
                ->where('related_id', $issuedBook->id)
                ->exists();

            if ($existingReminder) {
                continue;
            }

            $daysOverdue = Carbon::parse($issuedBook->due_date)->diffInDays(Carbon::now());

            Notification::notify(
                user: $issuedBook->student->user,
                type: 'book.overdue',
                title: 'Overdue Book Reminder',
                message: "Your book '{$issuedBook->book->title}' is {$daysOverdue} day(s) overdue. Please return it immediately to avoid fines.",
                data: [
                    'book_id' => $issuedBook->book_id,
                    'issued_book_id' => $issuedBook->id,
                    'days_overdue' => $daysOverdue,
                    'due_date' => $issuedBook->due_date,
                ],
                relatedModel: 'IssuedBook',
                relatedId: $issuedBook->id
            );

            $count++;
        }

        $this->info("Sent {$count} overdue reminders.");
        return 0;
    }
}
