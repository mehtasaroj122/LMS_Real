<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SearchRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $students = Student::query()
            ->with(['user', 'department'])
            ->latest()
            ->paginate($this->perPage($request));

        return StudentResource::collection($students);
    }

    public function show(int $id): StudentResource
    {
        return new StudentResource(
            Student::query()->with(['user', 'department'])->findOrFail($id)
        );
    }

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = $request->string('q')->toString();

        $students = Student::query()
            ->with(['user', 'department'])
            ->where('roll_no', 'like', "%{$query}%")
            ->orWhere('student_id', 'like', "%{$query}%")
            ->orWhereHas('user', function ($builder) use ($query) {
                $builder
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orWhereHas('department', fn ($builder) => $builder->where('name', 'like', "%{$query}%"))
            ->orderBy('roll_no')
            ->paginate($this->perPage($request));

        return StudentResource::collection($students);
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
