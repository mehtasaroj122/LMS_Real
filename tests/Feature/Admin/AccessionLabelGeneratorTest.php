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

function accessionLabelBook(array $overrides = []): Book
{
    $key = Str::lower(Str::random(8));
    $category = Category::create([
        'name' => 'Label Category '.$key,
        'description' => 'Barcode label test category',
    ]);

    return Book::create(array_merge([
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
    ], $overrides));
}

function accessionLabelCopy(Book $book, string $accession, array $overrides = []): BookCopy
{
    return BookCopy::create(array_merge([
        'book_id' => $book->id,
        'accession_number' => $accession,
        'status' => 'available',
        'condition' => 'good',
        'book_type' => 'borrowing',
    ], $overrides));
}

function accessionPrintSettings(array $overrides = []): array
{
    return array_merge([
        'label_size' => 'medium',
        'columns' => 3,
        'page_size' => 'A4',
        'orientation' => 'portrait',
        'barcode_height' => 64,
        'copies_per_label' => 1,
        'show_accession' => true,
        'show_library_name' => false,
        'show_logo' => false,
        'show_border' => true,
    ], $overrides);
}

test('async accession workspace is visible to admins and blocked for students', function () {
    $admin = accessionLabelAdmin();

    $this->actingAs($admin)
        ->get(route('admin.accession-labels.index'))
        ->assertOk()
        ->assertSee('Start + Quantity')
        ->assertSee('Reprint Existing')
        ->assertSee('window.accessionLabelConfig', false)
        ->assertSee('ACC-000001');

    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student)->get(route('admin.accession-labels.index'))->assertForbidden();
    $this->actingAs($student)->getJson(route('admin.accession-labels.search'))->assertForbidden();
});

test('quantity preview skips existing accessions and continues until printable quantity is fulfilled', function () {
    $admin = accessionLabelAdmin();
    $book = accessionLabelBook();
    accessionLabelCopy($book, 'ACC-000003');
    accessionLabelCopy($book, 'ACC-000005');

    $this->actingAs($admin)
        ->postJson(route('admin.accession-labels.preview'), [
            'method' => 'quantity',
            'start' => 'acc-000001',
            'quantity' => 5,
            'skip_existing' => true,
        ])
        ->assertOk()
        ->assertJsonPath('requested_quantity', 5)
        ->assertJsonPath('generated_count', 5)
        ->assertJsonPath('skipped_count', 2)
        ->assertJsonPath('last_scanned', 'ACC-000007')
        ->assertJsonPath('labels.0.accession', 'ACC-000001')
        ->assertJsonPath('labels.4.accession', 'ACC-000007')
        ->assertJsonPath('skipped.0', 'ACC-000003')
        ->assertJsonPath('skipped.1', 'ACC-000005');

    expect(BookCopy::query()->count())->toBe(2)
        ->and(ActivityLog::query()->where('action', 'accession_barcodes_generated')->exists())->toBeTrue();
});

test('quantity preview can use raw range semantics and range mode remains supported', function () {
    $admin = accessionLabelAdmin();
    $book = accessionLabelBook();
    accessionLabelCopy($book, 'ACC-000002');

    $this->actingAs($admin)
        ->postJson(route('admin.accession-labels.preview'), [
            'method' => 'quantity', 'start' => 'ACC-000001', 'quantity' => 3, 'skip_existing' => false,
        ])
        ->assertOk()
        ->assertJsonPath('generated_count', 2)
        ->assertJsonPath('last_scanned', 'ACC-000003');

    $this->actingAs($admin)
        ->postJson(route('admin.accession-labels.preview'), [
            'method' => 'range', 'from' => 'ACC-000001', 'to' => 'ACC-000004',
        ])
        ->assertOk()
        ->assertJsonPath('requested_quantity', 4)
        ->assertJsonPath('generated_count', 3)
        ->assertJsonPath('skipped.0', 'ACC-000002');
});

test('async generation validates quantities formats reversed ranges and configured limits', function () {
    $admin = accessionLabelAdmin();

    $this->actingAs($admin)->postJson(route('admin.accession-labels.preview'), [
        'method' => 'quantity', 'start' => 'BAD-1', 'quantity' => 0, 'skip_existing' => true,
    ])->assertUnprocessable()->assertJsonValidationErrors(['start', 'quantity']);

    $this->actingAs($admin)->postJson(route('admin.accession-labels.preview'), [
        'method' => 'range', 'from' => 'ACC-000010', 'to' => 'ACC-000001',
    ])->assertUnprocessable()->assertJsonValidationErrors('to');

    config()->set('accession-labels.max_per_batch', 3);
    $this->actingAs($admin)->postJson(route('admin.accession-labels.preview'), [
        'method' => 'quantity', 'start' => 'ACC-000001', 'quantity' => 4, 'skip_existing' => true,
    ])->assertUnprocessable()->assertJsonValidationErrors('quantity');
});

test('reprint search returns matching books first and then paginates their physical copies', function () {
    $admin = accessionLabelAdmin();
    $cleanCode = accessionLabelBook(['title' => 'Clean Code Reference']);
    $other = accessionLabelBook(['title' => 'Other Book']);
    accessionLabelCopy($cleanCode, 'ACC-000015', ['status' => 'issued', 'condition' => 'fair']);
    accessionLabelCopy($cleanCode, 'ACC-000016', ['status' => 'available']);
    accessionLabelCopy($other, 'ACC-000017');

    $this->actingAs($admin)
        ->getJson(route('admin.accession-labels.search', [
            'q' => 'Clean Code', 'status' => 'issued', 'sort' => 'book_title', 'per_page' => 10,
        ]))
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.title', 'Clean Code Reference')
        ->assertJsonPath('data.0.copies_count', 2)
        ->assertJsonPath('data.0.available_copies_count', 1);

    $this->actingAs($admin)
        ->getJson(route('admin.accession-labels.search', ['q' => 'ACC-000015']))
        ->assertOk()
        ->assertJsonPath('data.0.id', $cleanCode->id)
        ->assertJsonPath('data.0.matched_accession', 'ACC-000015');

    $this->actingAs($admin)
        ->getJson(route('admin.accession-labels.book-copies', [
            'book' => $cleanCode, 'condition' => 'fair', 'per_page' => 10,
        ]))
        ->assertOk()
        ->assertJsonPath('book.title', 'Clean Code Reference')
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.accession', 'ACC-000015')
        ->assertJsonPath('data.0.condition', 'fair');

    $this->actingAs($admin)
        ->getJson(route('admin.accession-labels.book-copies', [
            'book' => $cleanCode, 'highlight' => 'ACC-000016', 'per_page' => 10,
        ]))
        ->assertOk()
        ->assertJsonPath('data.0.accession', 'ACC-000016');
});

test('bulk existing preview returns shared vector barcodes and reports missing ids', function () {
    $admin = accessionLabelAdmin();
    $book = accessionLabelBook();
    $first = accessionLabelCopy($book, 'ACC-000015');
    $second = accessionLabelCopy($book, 'ACC-000016');

    $this->actingAs($admin)
        ->postJson(route('admin.accession-labels.existing-preview'), ['copy_ids' => [$first->id, $second->id, 999999]])
        ->assertOk()
        ->assertJsonCount(2, 'labels')
        ->assertJsonPath('labels.0.accession', 'ACC-000015')
        ->assertJsonPath('missing_ids.0', 999999)
        ->assertJsonFragment(['book_title' => $book->title]);
});

test('generated print preparation rechecks availability and returns an async print document', function () {
    $admin = accessionLabelAdmin();
    $book = accessionLabelBook();
    accessionLabelCopy($book, 'ACC-000002');

    $payload = accessionPrintSettings([
        'source' => 'generated',
        'method' => 'range',
        'from' => 'ACC-000001',
        'to' => 'ACC-000003',
        'print_scope' => 'selected',
        'accessions' => ['ACC-000001', 'ACC-000002', 'ACC-999999'],
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.accession-labels.print'), $payload)
        ->assertOk()
        ->assertJsonPath('summary.unique_count', 1)
        ->assertJsonPath('summary.total_labels', 1)
        ->assertJsonPath('accessions.0', 'ACC-000001')
        ->assertJsonFragment(['skipped_now' => ['ACC-000002', 'ACC-999999']])
        ->assertJson(fn ($json) => $json->whereType('html', 'string')->etc());

    expect(ActivityLog::query()->where('action', 'accession_barcodes_printed')->exists())->toBeTrue();
});

test('bulk reprint preparation supports duplicate label copies without creating book records', function () {
    $admin = accessionLabelAdmin();
    $book = accessionLabelBook();
    $first = accessionLabelCopy($book, 'ACC-000041');
    $second = accessionLabelCopy($book, 'ACC-000042');

    $payload = accessionPrintSettings([
        'source' => 'reprint',
        'copy_ids' => [$first->id, $second->id],
        'copies_per_label' => 2,
        'orientation' => 'landscape',
    ]);

    $response = $this->actingAs($admin)->postJson(route('admin.accession-labels.print'), $payload);
    $response->assertOk()
        ->assertJsonPath('summary.unique_count', 2)
        ->assertJsonPath('summary.copies_per_label', 2)
        ->assertJsonPath('summary.total_labels', 4)
        ->assertJsonPath('summary.orientation', 'landscape');

    expect($response->json('html'))->toContain('A4 landscape')
        ->and(BookCopy::query()->count())->toBe(2)
        ->and(ActivityLog::query()->where('action', 'bulk_accession_barcodes_reprinted')->exists())->toBeTrue();
});
