<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/users.json");
        $users = collect(json_decode($json, true));

        $users->each(function ($user) {
            User::create([
                'role' => $user['role'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'gender' => $user['gender'] ?? null,
                'profile_photo' => $user['profile_photo'],
                'password' => bcrypt($user['password']),
                'status' => $user['status'],
            ]);
        });
    }
}
