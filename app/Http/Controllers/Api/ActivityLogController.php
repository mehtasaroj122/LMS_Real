<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityLogController extends Controller
{
    use ResolvesApiUsers;

    public function __invoke(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if ($forbidden = $this->ensureRole($request, ['admin', 'staff'])) {
            return $forbidden;
        }

        $query = ActivityLog::query()->latest();

        if ($request->user()->role === 'staff') {
            $query->where(function ($builder) use ($request) {
                $builder
                    ->where('user_id', $request->user()->id)
                    ->orWhere('user_role', 'staff')
                    ->orWhereIn('action_category', ['book', 'book_request', 'fine']);
            });
        }

        return ActivityLogResource::collection(
            $query->paginate($this->perPage($request))
        );
    }
}
