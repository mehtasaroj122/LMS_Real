<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\AuditDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('access-admin');
        $perPage = $this->normalizeAdminPerPage($request->input('per_page', 10));

        $query = ActivityLog::with('user')->latest('created_at');

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('user_role', $request->role);
        }

        // Filter by period
        if ($request->has('period') && $request->period) {
            $period = $request->period;
            $now = Carbon::now();

            switch ($period) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case '7days':
                    $query->whereBetween('created_at', [
                        $now->copy()->subDays(7),
                        $now,
                    ]);
                    break;
                case '30days':
                    $query->whereBetween('created_at', [
                        $now->copy()->subDays(30),
                        $now,
                    ]);
                    break;
                case 'all':
                default:
                    break;
            }
        }

        // Filter by action category
        if ($request->has('action_category') && $request->action_category) {
            $query->where('action_category', $request->action_category);
        }

        if ($request->filled('event_type')) {
            $query->where('action', $request->input('event_type'));
        }

        // Get statistics
        $allActivities = ActivityLog::count();
        $totalActivities = $query->count();
        $adminActions = ActivityLog::where('user_role', 'admin')->count();
        $staffActions = ActivityLog::where('user_role', 'staff')->count();
        $studentActions = ActivityLog::where('user_role', 'student')->count();

        // Paginate results
        $activities = $query->paginate($perPage)->appends($request->query());
        $auditDetails = AuditDetails::forLogs($activities->getCollection());

        // Get action categories for filter dropdown
        $actionCategories = ActivityLog::select('action_category')
            ->whereNotNull('action_category')
            ->where('action_category', '!=', '')
            ->distinct()
            ->orderBy('action_category')
            ->pluck('action_category');
        $eventTypes = ActivityLog::query()->whereNotNull('action')->where('action', '!=', '')
            ->distinct()->orderBy('action')->pluck('action');

        // Get action statistics for summary (counts ALL actions from all users)
        $actionStats = ActivityLog::query()
            ->select('action')
            ->selectRaw('count(*) as count')
            ->selectRaw('MIN(action_category) as action_category')
            ->groupBy('action')
            ->orderByRaw('count DESC')
            ->orderBy('action')
            ->limit(12)
            ->get();

        return view('Admin.ActivityLogs', compact(
            'activities',
            'totalActivities',
            'adminActions',
            'staffActions',
            'studentActions',
            'allActivities',
            'actionCategories',
            'actionStats',
            'eventTypes',
            'auditDetails'
        ));
    }

    private function normalizeAdminPerPage($value): int
    {
        $allowedValues = [10, 20, 50, 100];
        $perPage = (int) $value;

        return in_array($perPage, $allowedValues, true) ? $perPage : 10;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
