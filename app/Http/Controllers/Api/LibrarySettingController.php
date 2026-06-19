<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibrarySettingController extends Controller
{
    use ResolvesApiUsers;

    public function __invoke(Request $request): JsonResponse
    {
        $setting = $this->activeFineSetting();

        return response()->json([
            'message' => 'Library settings loaded successfully.',
            'data' => [
                'max_books' => (int) $setting->max_books_per_student,
                'borrow_days' => (int) $setting->issue_duration_days,
                'fine_per_day' => (float) $setting->per_day_fine,
                'grace_days' => (int) $setting->grace_period_days,
                'library_name' => config('app.name', 'Library Management System'),
            ],
        ]);
    }
}
