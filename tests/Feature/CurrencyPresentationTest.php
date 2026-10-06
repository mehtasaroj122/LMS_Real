<?php

use App\Models\ActivityLog;
use App\Models\Fine;
use App\Models\Notification;
use App\Models\User;
use App\Support\AuditDescription;
use App\Support\Currency;

test('legacy audit and notification presentation preserves stored text and numeric metadata', function () {
    $user = User::factory()->create();
    $legacy = "Adjusted from \u{20B9}29.00 to Rs. 35.00 (+\u{20B9}6.00), balance INR 1,500.00";
    $expected = 'Adjusted from रु 29.00 to रु 35.00 (+रु 6.00), balance रु 1,500.00';
    $log = ActivityLog::create([
        'user_id' => $user->id, 'action' => 'fine_adjusted', 'action_category' => 'fine',
        'description' => $legacy, 'metadata' => ['old_amount' => 29, 'new_amount' => 35, 'amount_change' => 6],
    ]);
    $notification = Notification::create([
        'user_id' => $user->id, 'type' => 'fine.created', 'title' => 'Fine adjusted',
        'message' => $legacy, 'data' => ['amount' => 35],
    ]);
    $log->refresh();
    $notification->refresh();
    $logBefore = $log->getAttributes();
    $notificationBefore = $notification->getAttributes();

    expect($log->readable_description)->toBe($expected)
        ->and(implode('', array_column(AuditDescription::segments($log), 'text')))->toBe($expected)
        ->and($notification->message)->toBe($expected)
        ->and($notification->toArray()['message'])->toBe($expected)
        ->and($notification->icon)->toBe('coins')
        ->and($log->metadata['new_amount'])->toBe(35)
        ->and($notification->data['amount'])->toBe(35)
        ->and($log->fresh()->getAttributes())->toBe($logBefore)
        ->and($notification->fresh()->getAttributes())->toBe($notificationBefore)
        ->and(Currency::normalizeText('Rupee textbook; Rs author; INR catalogue'))->toBe('Rupee textbook; Rs author; INR catalogue');
});

test('fine history recovers identical original amounts from old and new recorded labels', function (string $symbol) {
    $fine = new Fine(['amount' => 35, 'remarks' => "Adjusted from {$symbol}29.00 to {$symbol}35.00"]);
    $before = $fine->getAttributes();
    $method = new ReflectionMethod(App\Http\Controllers\Admin\FineController::class, 'resolveOriginalFineAmount');
    $controller = app(App\Http\Controllers\Admin\FineController::class);

    expect($method->invoke($controller, $fine, collect()))->toBe(29.0)
        ->and($fine->getAttributes())->toBe($before);
})->with(["\u{20B9}", 'रु ', 'Rs. ', 'INR ', '']);

test('currency presentation retains precision, grouping and signed transitions', function () {
    expect(Currency::format(1500))->toBe('रु 1,500.00')
        ->and(Currency::format(3.5))->toBe('रु 3.50')
        ->and(Currency::format(500, 0))->toBe('रु 500')
        ->and(Currency::normalizeText("\u{20B9}500 → \u{20B9}300 (-\u{20B9}200)"))->toBe('रु 500 → रु 300 (-रु 200)');
});
