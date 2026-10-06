<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Support\AuditEventStyle;
use App\Support\LibraryBranding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

test('audit event families and outcomes have consistent presentation', function ($event, $category, $tone, $meaning) {
    $style = AuditEventStyle::resolve($event, $category, 'completed');
    expect($style['tone'])->toBe($tone)->and($style['severity'])->toBe($meaning);
})->with([
    ['fine_applied', 'fine', 'fine', 'warning'], ['fine_adjusted', 'fine', 'fine', 'warning'],
    ['fine_paid', 'fine', 'success', 'success'], ['fine_waived', 'fine', 'user', 'success'],
    ['login', 'auth', 'auth', 'routine'], ['failed_login', 'auth', 'danger', 'danger'],
    ['otp_verified', 'auth', 'success', 'success'], ['book_issued', 'book', 'book', 'routine'],
    ['book_returned', 'book', 'success', 'success'], ['book_deleted', 'book', 'danger', 'danger'],
    ['book_lost', 'book', 'danger', 'danger'], ['user_created', 'user', 'user', 'routine'],
    ['account_activated', 'user', 'success', 'success'], ['account_deactivated', 'user', 'danger', 'danger'],
    ['privilege_updated', 'privilege', 'privilege', 'routine'],
    ['borrowing_permission_changed', 'user', 'privilege', 'routine'],
    ['book_request_submitted', 'book_request', 'request', 'routine'],
    ['book_request_approved', 'book_request', 'success', 'success'],
    ['book_request_rejected', 'book_request', 'danger', 'danger'],
    ['library_settings_updated', 'settings', 'system', 'routine'],
    ['unknown_custom_event', null, 'system', 'routine'],
]);

test('audit center preserves counts filtering pagination global analytics and records', function () {
    View::share('libraryBranding', LibraryBranding::resolve());
    $this->travelTo(now()->setDate(2026, 10, 6)->setTime(12, 0));
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Saroj Mehta']);
    $staff = User::factory()->create(['role' => 'staff', 'name' => 'Library Staff']);
    $student = User::factory()->create(['role' => 'student', 'name' => 'Saif Khan']);
    $category = Category::create(['name' => 'Science']);
    $book = Book::create(['category_id' => $category->id, 'title' => 'Spectroscopy', 'isbn' => 'AUDIT-BOOK', 'author' => 'Test Author', 'total_copies' => 1, 'available_copies' => 1]);
    $events = [
        ['fine_applied', 'fine', $admin, 'Fine of ₹500 applied for “Spectroscopy” to Saif Khan (CHEM-2023-010)'],
        ['login', 'auth', $student, 'Saif Khan logged in'],
        ['book_created', 'book', $staff, 'Book “Spectroscopy” created'],
        ['book_returned', 'book', $staff, 'Book “Spectroscopy” returned'],
        ['privilege_updated', 'privilege', $admin, 'Library privileges updated'],
        ['user_created', 'user', $admin, 'Account created for Saif Khan'],
        ['library_settings_updated', 'system', $admin, 'Library settings changed'],
        ['book_deleted', 'book', $admin, 'Book removed'],
        ['fine_paid', 'fine', $student, 'Fine of ₹500 paid for “Spectroscopy”'],
        ['book_request_submitted', 'book_request', $student, 'Book requested'],
        ['failed_login', 'auth', $student, 'Authentication failed'],
        ['custom_event', 'general', $admin, 'Custom recorded activity'],
    ];
    foreach ($events as $index => [$action, $family, $actor, $description]) {
        $log = ActivityLog::create(['user_id' => $actor->id, 'user_name' => $actor->name, 'user_email' => $actor->email,
            'user_role' => $actor->role, 'action' => $action, 'action_category' => $family, 'status' => 'completed',
            'description' => $description, 'browser' => 'Firefox', 'device_type' => 'desktop', 'ip_address' => '127.0.0.1',
            'resource_type' => 'book', 'resource_id' => $action === 'book_deleted' ? 99999 : $book->id,
            'metadata' => ['book_name' => 'Spectroscopy', 'student_name' => 'Saif Khan', 'remarks' => 'Recorded review'],
        ]);
        $log->created_at = $index === 11 ? now()->subDays(40) : now()->subMinutes($index);
        $log->save();
    }
    $before = ActivityLog::orderBy('id')->get()->toArray();
    $url = route('admin.activity-logs.index');
    $response = $this->actingAs($admin)->get($url)->assertOk()
        ->assertSee('12 total audit entries')->assertSee('Most Frequent Events')
        ->assertSee('View details')->assertSee('Audit Event Details');
    $data = $response->original->getData();
    expect($data['allActivities'])->toBe(12)->and($data['totalActivities'])->toBe(12)
        ->and($data['adminActions'])->toBe(6)->and($data['staffActions'])->toBe(2)->and($data['studentActions'])->toBe(4)
        ->and($data['activities']->count())->toBe(10)->and($data['actionStats']->sum('count'))->toBe(12);
    expect($data['auditDetails']->first()['resourceAvailable'])->toBeTrue()
        ->and($data['auditDetails']->first()['eventStyle']['tone'])->toBe('fine');
    $deleted = $data['activities']->firstWhere('action', 'book_deleted');
    expect($data['auditDetails'][$deleted->id]['resourceAvailable'])->toBeFalse();

    $variants = [
        'role=admin' => 6, 'action_category=fine' => 2, 'event_type=fine_applied' => 1,
        'period=today' => 11, 'period=7days' => 11, 'period=30days' => 11,
        'search=Spectroscopy' => 4, 'search='.urlencode($student->email) => 4,
        'role=student&action_category=fine&event_type=fine_paid&period=7days' => 1,
        'search=does-not-exist' => 0,
    ];
    foreach ($variants as $query => $expected) {
        $filtered = $this->get($url.'?'.$query)->assertOk();
        expect($filtered->original->getData()['totalActivities'])->toBe($expected)
            ->and($filtered->original->getData()['allActivities'])->toBe(12)
            ->and($filtered->original->getData()['actionStats']->sum('count'))->toBe(12);
        if ($expected === 0) {
            $filtered->assertSee('No activity logs found')->assertSee('Reset Filters');
        }
        if (getenv('AUDIT_BROWSER_FIXTURES')) {
            file_put_contents(storage_path('framework/testing/audit-center-'.md5($query).'.html'), $filtered->getContent());
        }
    }
    $page2 = $this->get($url.'?page=2')->assertOk();
    expect($page2->original->getData()['activities']->count())->toBe(2);
    foreach ([10, 20, 50, 100, 999] as $size) {
        $page = $this->get($url.'?per_page='.$size)->assertOk()->original->getData()['activities'];
        expect($page->perPage())->toBe($size === 999 ? 10 : $size);
    }
    DB::enableQueryLog();
    $this->get($url.'?per_page=100')->assertOk();
    $queries = collect(DB::getQueryLog());
    DB::disableQueryLog();
    expect($queries->filter(fn ($query) => str_contains($query['query'], 'from "books"'))->count())->toBe(1)
        ->and(ActivityLog::orderBy('id')->get()->toArray())->toBe($before);
    if (getenv('AUDIT_BROWSER_FIXTURES')) {
        file_put_contents(storage_path('framework/testing/audit-center.html'), $response->getContent());
        file_put_contents(storage_path('framework/testing/audit-center-page2.html'), $page2->getContent());
        file_put_contents(storage_path('framework/testing/audit-center-all.html'), $this->get($url.'?per_page=20')->getContent());
    }
    $this->actingAs($student)->get($url)->assertForbidden();
    $this->actingAs($staff)->get($url)->assertForbidden();
});

test('audit center handles an empty system and metadata-driven account severity', function () {
    Illuminate\Support\Facades\View::share('libraryBranding', App\Support\LibraryBranding::resolve());
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('admin.activity-logs.index'))->assertOk()
        ->assertSee('0 total audit entries')->assertSee('No activity logs found')->assertSee('No event analytics yet');
    expect(AuditEventStyle::resolve('status_changed', 'user', 'completed', ['new_status' => 'inactive'])['tone'])->toBe('danger')
        ->and(AuditEventStyle::resolve('status_changed', 'user', 'completed', ['new_status' => 'active'])['tone'])->toBe('success');
});
