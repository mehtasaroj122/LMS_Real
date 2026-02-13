<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test {user_id?}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create a test notification for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }

        // Create a test notification
        Notification::notify(
            user: $user,
            type: 'book.overdue',
            title: 'Book Overdue',
            message: 'Your book "The Great Gatsby" is now overdue by 2 days.',
            data: ['book_id' => 1, 'days_overdue' => 2],
            relatedModel: 'Book',
            relatedId: 1
        );

        $this->info("✅ Test notification created successfully for {$user->name}!");
    }
}
