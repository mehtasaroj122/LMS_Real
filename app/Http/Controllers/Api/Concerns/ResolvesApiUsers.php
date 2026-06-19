<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\FineSetting;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ResolvesApiUsers
{
    protected function forbid(): JsonResponse
    {
        return response()->json(['message' => 'Forbidden.'], 403);
    }

    protected function ensureRole(Request $request, array|string $roles): ?JsonResponse
    {
        $roles = (array) $roles;

        return in_array((string) $request->user()?->role, $roles, true)
            ? null
            : $this->forbid();
    }

    protected function authenticatedStudent(Request $request): Student|JsonResponse
    {
        if ($request->user()?->role !== 'student') {
            return $this->forbid();
        }

        $student = $request->user()->student()
            ->with(['user', 'department', 'privileges'])
            ->first();

        if (! $student) {
            return response()->json(['message' => 'Student profile not found.'], 404);
        }

        return $student;
    }

    protected function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }

    protected function activeFineSetting(): FineSetting
    {
        return FineSetting::resolveActive();
    }
}
