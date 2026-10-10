<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Services\PhysicalBookCopyService;
use Laravel\Sanctum\Sanctum;

function conditionSummaryBook(array $counts, string $stored = 'good'): Book
{
    $category = Category::create(['name' => 'Condition Category '.str()->uuid()]);
    $book = Book::create([
        'category_id' => $category->id,
        'title' => 'Condition Summary Book',
        'author' => 'Condition Author',
        'isbn' => 'condition-'.str()->uuid(),
        'condition' => $stored,
        'total_copies' => array_sum($counts),
        'available_copies' => array_sum($counts),
        'status' => 'available',
    ]);
    $number = 0;
    foreach ($counts as $condition => $count) {
        for ($index = 0; $index < $count; $index++) {
            $number++;
            BookCopy::create([
                'book_id' => $book->id,
                'accession_number' => "CON-{$book->id}-{$number}",
                'condition' => $condition,
                // Issued and reference copies contribute to the title's condition too.
                'status' => $number % 2 ? 'issued' : 'available',
                'book_type' => $number % 2 ? 'borrowing' : 'reference',
            ]);
        }
    }

    return $book;
}

dataset('predominant book conditions', [
    'good is most common without an absolute majority' => [['good' => 3, 'new' => 2, 'damaged' => 2], 'good', 'damaged'],
    'new is most common' => [['new' => 4, 'good' => 2, 'damaged' => 1], 'new', 'good'],
    'damaged is most common' => [['damaged' => 4, 'good' => 1, 'new' => 2], 'damaged', 'new'],
    'damage wins a three way tie' => [['new' => 2, 'good' => 2, 'damaged' => 2], 'damaged', 'good'],
    'good wins a tie with new' => [['new' => 2, 'good' => 2], 'good', 'new'],
    'fair remains a distinct condition' => [['fair' => 3, 'good' => 1], 'fair', 'good'],
    'lost remains a distinct condition' => [['lost' => 3, 'damaged' => 1], 'lost', 'good'],
    'no physical copies preserves new' => [[], 'new', 'new'],
    'no physical copies preserves damaged' => [[], 'damaged', 'damaged'],
]);

test('book management badges details filters and statistics agree on the most common copy condition', function (
    string $role, array $counts, string $expected, string $stored
) {
    $book = conditionSummaryBook($counts, $stored);
    $this->actingAs(User::factory()->create(['role' => $role]));
    $indexRoute = $role === 'admin' ? 'admin.books.index' : 'staff.book-management.index';

    $this->get(route($indexRoute))->assertOk()
        ->assertViewHas('initialBooks', fn ($books) => $books->first()->display_condition === $expected)
        ->assertSee('data-condition="'.$expected.'"', false)
        ->assertSee('condition-badge condition-'.$expected, false);
    $this->getJson(route("{$role}.books.show", $book))->assertOk()
        ->assertJsonPath('data.book.condition', $expected);

    $response = $this->getJson(route("{$role}.books.data", ['condition' => $expected]))->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('stats.conditionBreakdown.'.$expected, 1);
    expect($response->json('tableRows'))->toContain('data-condition="'.$expected.'"')
        ->toContain('condition-badge condition-'.$expected);
    $this->getJson(route("{$role}.books.stats", ['condition' => $expected]))->assertOk()
        ->assertJsonPath('totalBooks', 1)
        ->assertJsonPath('conditionBreakdown.'.$expected, 1);

    $excluded = $expected === 'good' ? 'new' : 'good';
    $this->getJson(route("{$role}.books.data", ['condition' => $excluded]))->assertOk()
        ->assertJsonPath('total', 0)->assertJsonPath('stats.totalBooks', 0);
    $this->get(route($indexRoute, ['condition' => $excluded]))->assertOk()
        ->assertViewHas('initialBooks', fn ($books) => $books->total() === 0);
    expect($book->fresh()->condition)->toBe($stored);
})->with(['admin', 'staff'])->with('predominant book conditions');

test('condition summaries reflect copy additions edits returns and deletions on the next read', function (string $role) {
    $book = conditionSummaryBook(['new' => 2, 'good' => 1]);
    $this->actingAs(User::factory()->create(['role' => $role]));
    $url = route("{$role}.books.show", $book);
    $this->getJson($url)->assertOk()->assertJsonPath('data.book.condition', 'new');

    $newCopies = $book->copies()->where('condition', 'new')->get();
    $newCopies[0]->update(['condition' => 'damaged']);
    $this->getJson($url)->assertOk()->assertJsonPath('data.book.condition', 'damaged');
    $newCopies[0]->delete();
    $this->getJson($url)->assertOk()->assertJsonPath('data.book.condition', 'good');

    $copy = BookCopy::create([
        'book_id' => $book->id, 'accession_number' => 'CON-ADDED',
        'condition' => 'new', 'status' => 'available', 'book_type' => 'borrowing',
    ]);
    $this->getJson($url)->assertOk()->assertJsonPath('data.book.condition', 'new');
    // Returning a copy updates its condition through the normal circulation service.
    $studentUser = User::factory()->create(['role' => 'student', 'status' => 'active']);
    $department = Department::create(['name' => 'Condition Department', 'code' => 'CON', 'status' => 'active']);
    $student = Student::create([
        'user_id' => $studentUser->id, 'student_id' => 'CON-STUDENT', 'roll_no' => 'CON-ROLL',
        'department_id' => $department->id, 'semester' => '1',
    ]);
    $circulation = app(PhysicalBookCopyService::class);
    $circulation->issue($student, $copy->accession_number, auth()->user());
    $circulation->return($copy->accession_number, 'damaged');
    $this->getJson($url)->assertOk()->assertJsonPath('data.book.condition', 'damaged');
})->with(['admin', 'staff']);

test('catalogue API conditions and filters use the same physical copy summary', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'student', 'status' => 'active']));
    $book = conditionSummaryBook(['good' => 3, 'new' => 2, 'damaged' => 2], 'damaged');

    foreach ([
        '/api/books?condition=good',
        '/api/books/search?q=Condition&condition=good',
        '/api/books/category/'.$book->category_id,
    ] as $url) {
        $this->getJson($url)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $book->id)->assertJsonPath('data.0.condition', 'good');
    }
    $this->getJson('/api/books/'.$book->id)->assertOk()->assertJsonPath('data.condition', 'good');
    $this->getJson('/api/books?condition=damaged')->assertOk()->assertJsonCount(0, 'data');
});
