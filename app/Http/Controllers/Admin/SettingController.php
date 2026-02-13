<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FineSetting;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-admin');
        $user = Auth::user();
        $fineSetting = FineSetting::first() ?? new FineSetting();
        return view('admin.Settings', compact('user', 'fineSetting'));
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
    public function update(Request $request)
    {
        \Log::info('Settings update called');
        \Log::info('Request all:', $request->all());
        \Log::info('Has file:', ['has_file' => $request->hasFile('profile_photo')]);
        \Log::info('Expects JSON:', ['expects_json' => $request->expectsJson()]);
        \Log::info('Accept header:', ['accept' => $request->header('Accept')]);
        
        Gate::authorize('access-admin');
        
        $user = Auth::user();
        
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        \Log::info('Validation passed');

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            \Log::info('Processing photo upload');
            try {
                // Delete old photo if exists
                if ($user->profile_photo) {
                    // Extract the relative path from the stored value
                    $oldPhotoPath = $user->profile_photo;
                    
                    // Remove 'storage/' prefix if it exists to get the path relative to public disk
                    if (strpos($oldPhotoPath, 'storage/') === 0) {
                        $oldPhotoPath = substr($oldPhotoPath, 8); // Remove 'storage/' prefix
                    }
                    
                    // Delete from public disk
                    if (Storage::disk('public')->exists($oldPhotoPath)) {
                        Storage::disk('public')->delete($oldPhotoPath);
                        \Log::info('Deleted old photo from public disk: ' . $oldPhotoPath);
                    }
                }
                
                // Store new photo
                $file = $request->file('profile_photo');
                $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                \Log::info('Storing file', ['filename' => $filename]);
                
                // Store the file using public disk
                $file->storeAs('profile_pics', $filename, 'public');
                $validated['profile_photo'] = 'storage/profile_pics/' . $filename;
                
                \Log::info('File stored successfully: storage/profile_pics/' . $filename);
            } catch (\Exception $e) {
                \Log::error('Profile photo upload failed: ' . $e->getMessage(), ['exception' => $e]);
                if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload photo: ' . $e->getMessage(),
                    ], 422);
                }
                return back()->withErrors(['profile_photo' => 'Failed to upload photo: ' . $e->getMessage()]);
            }
        }

        // Update user
        \Log::info('Updating user with validated data:', $validated);
        $user->update($validated);

        // Log the activity
        $changedFields = array_keys($validated);
        ActivityLogger::logActivity(
            'profile_updated',
            "Admin profile updated: " . implode(', ', $changedFields),
            'user',
            'user',
            $user->id,
            ['changed_fields' => $changedFields]
        );

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user,
            ]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Profile updated successfully');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        Gate::authorize('access-admin');
        
        $user = Auth::user();
        
        // Validate input
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ], [
            'current_password.required' => 'Current password is required',
            'new_password.required' => 'New password is required',
            'new_password.min' => 'New password must be at least 8 characters',
            'new_password.confirmed' => 'Password confirmation does not match',
            'new_password.regex' => 'Password must contain uppercase, lowercase, and a number',
        ]);

        // Check if current password is correct
        if (!\Hash::check($validated['current_password'], $user->password)) {
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'The current password is incorrect',
                ], 422);
            }
            return back()->withErrors(['current_password' => 'The current password is incorrect']);
        }

        // Update the password
        $user->update([
            'password' => \Hash::make($validated['new_password']),
        ]);

        // Log the activity
        ActivityLogger::logActivity(
            'password_reset',
            "Admin password changed",
            'auth',
            'user',
            $user->id
        );

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
            ]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Password updated successfully');
    }

    /**
     * Update library settings (Fine settings)
     */
    public function updateLibrarySettings(Request $request)
    {
        Gate::authorize('access-admin');

        // Validate input
        $validated = $request->validate([
            'per_day_fine' => 'required|numeric|min:0|max:9999.99',
            'grace_period_days' => 'required|integer|min:0|max:365',
            'max_fine_amount' => 'nullable|numeric|min:0|max:99999.99',
            'lost_book_penalty' => 'required|numeric|min:0|max:99999.99',
            'damaged_book_penalty' => 'nullable|numeric|min:0|max:99999.99',
            'issue_duration_days' => 'required|integer|min:1|max:365',
            'max_books_per_student' => 'required|integer|min:1|max:100',
        ]);

        // Get or create fine setting
        $fineSetting = FineSetting::first();
        if (!$fineSetting) {
            $fineSetting = new FineSetting();
        }

        // Update fine settings
        $fineSetting->per_day_fine = $validated['per_day_fine'];
        $fineSetting->grace_period_days = $validated['grace_period_days'];
        $fineSetting->max_fine_amount = $validated['max_fine_amount'];
        $fineSetting->lost_book_penalty = $validated['lost_book_penalty'];
        $fineSetting->damaged_book_penalty = $validated['damaged_book_penalty'];
        $fineSetting->issue_duration_days = $validated['issue_duration_days'];
        $fineSetting->max_books_per_student = $validated['max_books_per_student'];
        $fineSetting->is_active = 1;
        $fineSetting->save();

        // Log the activity
        ActivityLogger::logActivity(
            'library_settings_updated',
            "Library fine settings updated",
            'system',
            'fine_settings',
            $fineSetting->id,
            [
                'per_day_fine' => $validated['per_day_fine'],
                'grace_period_days' => $validated['grace_period_days'],
                'max_fine_amount' => $validated['max_fine_amount'],
                'lost_book_penalty' => $validated['lost_book_penalty']
            ]
        );

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Library settings updated successfully',
                'data' => $fineSetting,
            ]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Library settings updated successfully');
    }

    /**
     * Remove profile photo
     */
    public function removePhoto(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->profile_photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile photo to remove'
                ], 400);
            }

            // Delete the file from storage
            $photoPath = $user->profile_photo;
            
            // Remove 'storage/' prefix if it exists to get the path relative to public disk
            if (strpos($photoPath, 'storage/') === 0) {
                $photoPath = substr($photoPath, 8); // Remove 'storage/' prefix
            }
            
            // Delete from public disk
            if (Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
                \Log::info('Deleted profile photo from public disk: ' . $photoPath);
            }

            // Update user record
            $user->update(['profile_photo' => null]);

            // Log the activity
            ActivityLogger::logActivity(
                'profile_photo_removed',
                'Admin removed profile photo',
                'user',
                'user',
                $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to remove photo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
