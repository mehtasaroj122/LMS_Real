<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('access-staff');
        
        $query = ActivityLog::with('user')->latest('created_at');
        
        // Exclude admin activities - staff can only see student and staff logs
        $query->whereIn('user_role', ['staff', 'student']);

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
                        $now
                    ]);
                    break;
                case '30days':
                    $query->whereBetween('created_at', [
                        $now->copy()->subDays(30),
                        $now
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

        // Get statistics - only for staff and student activities
        $allActivities = ActivityLog::whereIn('user_role', ['staff', 'student'])->count();
        $totalActivities = $query->count();
        $adminActions = ActivityLog::where('user_role', 'admin')->count();
        $staffActions = ActivityLog::where('user_role', 'staff')->count();
        $studentActions = ActivityLog::where('user_role', 'student')->count();

        // Paginate results
        $activities = $query->paginate(15)->appends($request->query());

        // Get action categories for filter dropdown - only from staff and student activities
        $actionCategories = ActivityLog::whereIn('user_role', ['staff', 'student'])
            ->select('action_category')
            ->distinct()
            ->pluck('action_category');

        // Get action statistics for summary - only from staff and student activities
        $actionStats = ActivityLog::whereIn('user_role', ['staff', 'student'])
            ->select('action')
            ->selectRaw('count(*) as count')
            ->groupBy('action')
            ->orderByRaw('count DESC')
            ->limit(12)
            ->get();

        return view('Staff.ActivityLog', compact(
            'activities',
            'totalActivities',
            'adminActions',
            'staffActions',
            'studentActions',
            'allActivities',
            'actionCategories',
            'actionStats'
        ));
    }
}
