<?php

use App\Helpers\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\department as Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('recording activity preserves all history beyond 500 entries', function (string $logger) {
    $this->travelTo(now()->setDate(2026, 10, 8)->setTime(12, 0));
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $student = Student::create([
        'user_id' => User::factory()->create(['role' => 'student'])->id,
        'department_id' => Department::create(['name' => 'Science', 'code' => 'SCI', 'status' => 'active'])->id,
        'roll_no' => 'RETENTION-001',
        'semester' => '1',
    ]);

    $history = [];
    for ($index = 0; $index < 500; $index++) {
        $createdAt = $index === 0 ? now()->subMonths(4) : now()->subDay()->addSeconds($index);
        $history[] = [
            'action' => 'historical_activity',
            'description' => "Historical activity {$index}",
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
    DB::table('activity_logs')->insert($history);
    $before = ActivityLog::orderBy('id')->get()->toArray();
    $historicalIds = array_column($before, 'id');

    for ($index = 0; $index < 2; $index++) {
        $log = $logger === 'student'
            ? ActivityLogger::logStudentActivity($student, 'profile_updated', "New activity {$index}", 'user')
            : ActivityLogger::logActivity('profile_updated', "New activity {$index}", 'user');

        expect($log)->toBeInstanceOf(ActivityLog::class);
        $this->assertDatabaseHas('activity_logs', ['id' => $log->id, 'description' => "New activity {$index}"]);
    }

    expect(ActivityLog::count())->toBe(502)
        ->and(ActivityLog::whereIn('id', $historicalIds)->orderBy('id')->get()->toArray())->toBe($before);
})->with(['general', 'student']);
