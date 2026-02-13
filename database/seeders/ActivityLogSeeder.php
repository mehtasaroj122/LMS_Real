<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get some users to log activities for
        $users = User::limit(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please create users first.');
            return;
        }

        $actions = [
            'book_issued', 'book_returned', 'fine_payment', 'book_request_created', 
            'book_request_approved', 'status_changed', 'profile_updated', 
            'password_reset', 'login', 'logout', 'user_created', 'user_updated'
        ];

        $categories = [
            'book', 'fine', 'auth', 'user', 'book_request', 'system'
        ];

        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
        $devices = ['desktop', 'mobile', 'tablet'];

        $descriptions = [
            'Book "The Great Gatsby" issued to student',
            'Fine payment of ₹500 processed',
            'User logged in from new device',
            'Student profile updated with new address',
            'Book request approved for "1984"',
            'Fine for overdue book "To Kill a Mockingbird"',
            'Password changed successfully',
            'Book "Pride and Prejudice" returned',
            'User account status changed to inactive',
            'Fine waived due to special request',
        ];

        // Generate 50 sample activity logs
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $action = $actions[array_rand($actions)];
            $category = $categories[array_rand($categories)];
            
            ActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'user_email' => $user->email,
                'action' => $action,
                'action_category' => $category,
                'status' => 'completed',
                'model_type' => 'App\Models\Student',
                'model_id' => rand(1, 100),
                'description' => $descriptions[array_rand($descriptions)],
                'ip_address' => $faker->ipv4(),
                'browser' => $browsers[array_rand($browsers)],
                'device_type' => $devices[array_rand($devices)],
                'resource_type' => 'student',
                'resource_id' => rand(1, 100),
                'affected_user_id' => rand(1, 10),
                'created_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Activity logs seeded successfully!');
    }
}
