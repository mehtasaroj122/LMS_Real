<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

function accessionLabelAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function accessionLabelBook(): Book
{
    $key = Str::lower(Str::random(8));
    $category = Category::create([
        'name' => 'Label Category '.$key,
        'description' => 'Barcode label test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => 'Label Test Book '.$key,
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => '978'.random_int(1000000000, 9999999999),
        'total_copies' => 1,
        'available_copies' => 1,
        'condition' => 'good',
        'description' => 'Book used to verify barcode labels.',
        'shelf_no' => 'L-01',
        'status' => 'available',
    ]);
}

test('accession label generator is visible to admins and blocked for students', function () {
    $admin = accessionLabelAdmin();

    $this->actingAs($admin)
        ->get(route('admin.accession-labels.index'))
        ->assertOk()
        ->assertSee('Accession Number Generator')
        ->assertSee('ACC-000001')
        ->assertSee('data-lucide="barcode"', false);

    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student)
        ->get(route('admin.accession-labels.index'))
        ->assertForbidden();
});

test('range preview preserves format excludes existing copies and creates no copies', function () {
    $admin = accessionLabelAdmin();
    $book = accessionLabelBook();
    BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'ACC-000003',
        'status' => 'available',
        'condition' => 'good',
        'book_type' => 'borrowing',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.accession-labels.preview'), [
            'from' => 'acc-000001',
            'to' => 'ACC-000005',
        ])
        ->assertOk()
        ->assertSee('5')
        ->assertSee('4 printable labels')
        ->assertSee('ACC-000001')
        ->assertSee('ACC-000005')
        ->assertSee('ACC-000003')
        ->assertSee('Already exists')
        ->assertSee('Code 128 barcode for ACC-000001');

    expect(BookCopy::query()->count())->toBe(1)
        ->and(ActivityLog::query()->where('action', 'accession_barcodes_generated')->exists())->toBeTrue();
});

test('range validation rejects malformed reversed and excessive ranges', function () {
    $admin = accessionLabelAdmin();

    $this->actingAs($admin)
        ->post(route('admin.accession-labels.preview'), ['from' => 'BAD-1', 'to' => 'ACC-000002'])
        ->assertSessionHasErrors('from');

    $this->actingAs($admin)
        ->post(route('admin.accession-labels.preview'), ['from' => 'ACC-000010', 'to' => 'ACC-000001'])
        ->assertSessionHasErrors('to');

    config()->set('accession-labels.max_per_batch', 3);
    $this->actingAs($admin)
        ->post(route('admin.accession-labels.preview'), ['from' => 'ACC-000001', 'to' => 'ACC-000004'])
        ->assertSessionHasErrors('to');
});

test('print endpoint regenerates availability and only prints valid selected labels', function () {
    $admin = accessionLabelAdmin();
    $book = accessionLabelBook();
    BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'ACC-000002',
        'status' => 'available',
        'condition' => 'good',
        'book_type' => 'borrowing',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.accession-labels.print'), [
            'action_type' => 'range',
            'from' => 'ACC-000001',
            'to' => 'ACC-000003',
            'print_scope' => 'selected',
            'labels' => ['ACC-000001', 'ACC-000002', 'ACC-999999'],
            'label_size' => 'medium',
            'columns' => 3,
            'page_size' => 'A4',
            'show_accession' => 1,
            'show_library_name' => 0,
            'show_logo' => 0,
            'show_border' => 1,
        ])
        ->assertOk()
        ->assertSee('Accession Barcode Print Preview')
        ->assertSee('ACC-000001')
        ->assertDontSee('ACC-000002')
        ->assertDontSee('ACC-999999')
        ->assertDontSee('id="sidebar"', false);

    expect(ActivityLog::query()->where('action', 'accession_barcodes_printed')->exists())->toBeTrue();
});

test('existing copies can be searched and reprinted without creating records', function () {
    $admin = accessionLabelAdmin();
    $book = accessionLabelBook();
    $copy = BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'ACC-000042',
        'status' => 'available',
        'condition' => 'good',
        'book_type' => 'borrowing',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.accession-labels.index', ['mode' => 'reprint', 'q' => $book->title]))
        ->assertOk()
        ->assertSee($book->title)
        ->assertSee('ACC-000042');

    $this->actingAs($admin)
        ->post(route('admin.accession-labels.print'), [
            'action_type' => 'reprint',
            'copy_id' => $copy->id,
            'label_size' => 'large',
            'columns' => 5,
            'page_size' => 'Letter',
            'show_accession' => 1,
            'show_library_name' => 1,
            'show_logo' => 0,
            'show_border' => 1,
        ])
        ->assertOk()
        ->assertSee('ACC-000042')
        ->assertSee($book->title)
        ->assertSee('repeat(2, var(--label-width))', false);

    expect(BookCopy::query()->count())->toBe(1)
        ->and(ActivityLog::query()->where('action', 'accession_barcode_reprinted')->exists())->toBeTrue();
});
