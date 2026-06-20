<?php

use App\Models\BookRequest;
use App\Models\book as Book;
use App\Models\category as Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function makeMobileProfileUser(string $role = 'student'): User
{
    return User::create([
        'role' => $role,
        'name' => ucfirst($role) . ' Mobile User',
        'email' => $role . '-mobile-' . Str::random(8) . '@example.com',
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
        'email_verified_at' => now(),
    ]);
}

function makeMobileProfileStudent(): User
{
    $user = makeMobileProfileUser('student');
    $department = Department::create([
        'name' => 'Mobile Department ' . Str::random(6),
        'code' => Str::upper(Str::random(4)),
        'status' => 'active',
    ]);

    Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'MOB-' . Str::upper(Str::random(6)),
        'roll_no' => 'ROLL-' . Str::upper(Str::random(6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Test Address',
    ]);

    return $user->fresh()->load('student');
}

function makeMobileProfileBook(): Book
{
    $category = Category::create([
        'name' => 'Mobile Category ' . Str::random(6),
        'description' => 'Test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => 'Mobile Book ' . Str::random(6),
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => 'ISBN-' . Str::upper(Str::random(10)),
        'total_copies' => 5,
        'available_copies' => 4,
        'condition' => 'good',
        'status' => 'available',
    ]);
}

test('mobile user can upload and remove a profile photo', function () {
    Storage::fake('public');
    $user = makeMobileProfileStudent();
    Sanctum::actingAs($user);

    $temporaryImage = tempnam(sys_get_temp_dir(), 'profile-photo');
    file_put_contents(
        $temporaryImage,
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==')
    );

    $uploadResponse = $this->post('/api/profile/photo', [
        'photo' => new UploadedFile($temporaryImage, 'avatar.png', 'image/png', null, true),
    ], [
        'Accept' => 'application/json',
    ]);

    $uploadResponse
        ->assertOk()
        ->assertJsonPath('message', 'Profile photo uploaded successfully.')
        ->assertJsonStructure(['profile_photo', 'profile_photo_url']);

    $photoPath = $user->fresh()->profile_photo;
    expect($photoPath)->toStartWith('profile_photos/');
    Storage::disk('public')->assertExists($photoPath);

    $removeResponse = $this->deleteJson('/api/profile/photo');

    $removeResponse
        ->assertOk()
        ->assertJsonPath('message', 'Profile photo removed successfully.')
        ->assertJsonPath('profile_photo', null)
        ->assertJsonPath('profile_photo_url', null);

    expect($user->fresh()->profile_photo)->toBeNull();
    Storage::disk('public')->assertMissing($photoPath);
});

test('student delete eligibility reports active restrictions', function () {
    $user = makeMobileProfileStudent();
    $student = $user->student;
    $book = makeMobileProfileBook();

    $issue = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => null,
        'issue_date' => now()->subDays(3)->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'return_date' => null,
        'status' => 'issued',
        'condition' => 'good',
        'fine_amount' => 0,
    ]);

    Fine::create([
        'issued_book_id' => $issue->id,
        'student_id' => $student->id,
        'amount' => 50,
        'days_late' => 2,
        'status' => 'pending',
    ]);

    BookRequest::create([
        'student_id' => $student->id,
        'book_id' => $book->id,
        'request_date' => now(),
        'status' => 'approved',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/profile/delete-eligibility')
        ->assertOk()
        ->assertJsonPath('can_delete', false)
        ->assertJsonPath('issued_books', 1)
        ->assertJsonPath('pending_fines', 50)
        ->assertJsonPath('active_requests', 1);
});

test('eligible student account is deactivated from mobile', function () {
    $user = makeMobileProfileStudent();
    $user->createToken('android-mobile');
    Sanctum::actingAs($user);

    $this->deleteJson('/api/profile', [
        'confirmation' => 'DELETE',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Your account has been deactivated successfully.');

    $user->refresh();

    expect($user->status)->toBe('inactive');
    expect($user->tokens()->count())->toBe(0);
});

test('admin and staff accounts cannot be deleted from mobile', function () {
    foreach (['admin', 'staff'] as $role) {
        $user = makeMobileProfileUser($role);
        Sanctum::actingAs($user);

        $this->getJson('/api/profile/delete-eligibility')
            ->assertOk()
            ->assertJsonPath('can_delete', false)
            ->assertJsonPath('message', 'Admin and staff accounts cannot be deleted from mobile.');

        $this->deleteJson('/api/profile', [
            'confirmation' => 'DELETE',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Admin and staff accounts cannot be deleted from mobile.');
    }
});
