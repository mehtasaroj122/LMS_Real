<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);

        $this->call([
           DepartmentSeeder::class,
           userSeeder::class,
           categorySeeder::class,
           studentSeeder::class,
        //    StudentPrivilegeSeeder::class,
           staffSeeder::class,
           bookSeeder::class,
           BookRequestSeeder::class,
           issuedBookSeeder::class,
           fineSeeder::class,
        ]);
    }
}
