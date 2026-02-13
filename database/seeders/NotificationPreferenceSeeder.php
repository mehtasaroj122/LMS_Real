<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\NotificationPreference;
use Illuminate\Database\Seeder;

class NotificationPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Check if preference already exists
            if (!$user->notificationPreferences()->exists()) {
                NotificationPreference::create([
                    'user_id' => $user->id,
                    'book_overdue' => true,
                    'book_due_soon' => true,
                    'fine_created' => true,
                    'fine_reminder' => true,
                    'request_status_change' => true,
                    'new_book_available' => true,
                    'payment_confirmation' => true,
                ]);
            }
        }
    }
}
