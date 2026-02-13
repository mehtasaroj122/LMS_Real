<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Fine;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        Gate::authorize('access-student');
        
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)
            ->with('department')
            ->first();

        if (!$student) {
            return view('Student.Profile', [
                'user' => $user,
                'student' => null,
                'booksIssuedCount' => 0,
                'unpaidFinesCount' => 0,
                'pendingRequestsCount' => 0,
                'totalFinesAmount' => 0,
                'finesPaidAmount' => 0,
                'approvedRequestsCount' => 0,
            ]);
        }

        // Count books issued (not yet returned)
        $booksIssuedCount = $student->issuedBooks()->whereNull('return_date')->count();

        // Count unpaid fines
        $unpaidFinesCount = Fine::where('student_id', $student->id)
            ->where('status', 'unpaid')
            ->count();

        // Count pending requests
        $pendingRequestsCount = $student->bookRequests()->where('status', 'pending')->count();

        // Total fines amount
        $totalFinesAmount = Fine::where('student_id', $student->id)->sum('amount');

        // Fines paid amount
        $finesPaidAmount = Fine::where('student_id', $student->id)
            ->where('status', 'paid')
            ->sum('amount');

        // Approved requests count
        $approvedRequestsCount = $student->bookRequests()->where('status', 'approved')->count();

        return view('Student.Profile', [
            'user' => $user,
            'student' => $student,
            'booksIssuedCount' => $booksIssuedCount,
            'unpaidFinesCount' => $unpaidFinesCount,
            'pendingRequestsCount' => $pendingRequestsCount,
            'totalFinesAmount' => $totalFinesAmount,
            'finesPaidAmount' => $finesPaidAmount,
            'approvedRequestsCount' => $approvedRequestsCount,
        ]);
    }

    public function updatePersonalInfo(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        // Validate input
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        try {
            $oldEmail = $user->email;
            
            // Update user
            $user->update([
                'name' => $validated['fullName'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
            ]);

            // Notify user of profile update
            Notification::notify(
                user: $user,
                type: 'account.profile_updated',
                title: 'Profile Information Updated',
                message: 'Your personal information was updated',
                data: ['ip' => request()->ip()],
                relatedModel: 'User',
                relatedId: $user->id
            );

            // Notify of email change if applicable
            if ($oldEmail !== $validated['email']) {
                Notification::notify(
                    user: $user,
                    type: 'account.email_changed',
                    title: 'Email Address Changed',
                    message: 'Your email address was changed to ' . $validated['email'],
                    data: ['old_email' => $oldEmail, 'new_email' => $validated['email']],
                    relatedModel: 'User',
                    relatedId: $user->id
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Personal information updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating personal information. Please try again.',
            ], 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();

        // Validate input
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,gif|max:2048',
        ]);

        try {
            // Delete old photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Create directory if not exists
            if (!Storage::disk('public')->exists('Profile_pics')) {
                Storage::disk('public')->makeDirectory('Profile_pics');
            }

            // Store new photo
            $fileName = 'user_' . $user->id . '_' . time() . '.' . $request->file('photo')->getClientOriginalExtension();
            $path = $request->file('photo')->storeAs('Profile_pics', $fileName, 'public');

            // Update user profile_photo column
            $user->update([
                'profile_photo' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile photo uploaded successfully!',
                'photoUrl' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading photo. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();

        // Validate input
        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:6|confirmed',
            'newPassword_confirmation' => 'required|string|min:6',
        ]);

        try {
            // Check if current password matches
            if (!Hash::check($validated['currentPassword'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                    'field' => 'currentPassword',
                ], 422);
            }

            // Check if new password is different from current
            if (Hash::check($validated['newPassword'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from current password.',
                    'field' => 'newPassword',
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($validated['newPassword']),
            ]);

            // Notify user of password change
            Notification::notify(
                user: $user,
                type: 'account.password_changed',
                title: 'Password Changed Successfully',
                message: 'Your password was changed on ' . now()->format('M d, Y h:i A'),
                data: ['ip' => request()->ip(), 'timestamp' => now()],
                relatedModel: 'User',
                relatedId: $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating password. Please try again.',
            ], 500);
        }
    }

    public function removePhoto(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();

        try {
            // Delete the photo file if it exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Update user to remove photo reference
            $user->update([
                'profile_photo' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing photo. Please try again.',
            ], 500);
        }
    }

    public function edit()
    {
        Gate::authorize('access-student');
        return view('Student.Profile');
    }
}
