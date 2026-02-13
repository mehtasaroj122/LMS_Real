<?php

namespace App\Http\Controllers\Staff;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function index()
    {
        Gate::authorize('access-staff');
        $user = Auth::user();
        return view('Staff.Setting', compact('user'));
    }

    public function update(Request $request)
    {
        Gate::authorize('access-staff');
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            // delete old
            if ($user->profile_photo) {
                $old = $user->profile_photo;
                if (strpos($old, 'storage/') === 0) {
                    $old = substr($old, 8);
                }
                if (Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
            }

            $file = $request->file('profile_photo');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_pics', $filename, 'public');
            $validated['profile_photo'] = 'storage/profile_pics/' . $filename;
        }

        $user->update($validated);

        ActivityLogger::logActivity(
            'profile_updated',
            'Staff profile updated',
            'user',
            'user',
            $user->id,
            ['changed' => array_keys($validated)]
        );

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json(['success' => true, 'message' => 'Profile updated', 'user' => $user]);
        }

        return redirect()->route('staff.settings.index')->with('success', 'Profile updated successfully');
    }

    public function password(Request $request)
    {
        Gate::authorize('access-staff');
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password incorrect',
                    'errors' => ['current_password' => ['Current password incorrect']]
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Current password incorrect']);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        ActivityLogger::logActivity('password_changed', 'Staff changed password', 'auth', 'user', $user->id);

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json(['success' => true, 'message' => 'Password updated']);
        }

        return redirect()->route('staff.settings.index')->with('success', 'Password updated successfully');
    }

    public function photo(Request $request)
    {
        Gate::authorize('access-staff');
        $user = Auth::user();

        $validated = $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                $old = $user->profile_photo;
                if (strpos($old, 'storage/') === 0) {
                    $old = substr($old, 8);
                }
                if (Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
            }

            $file = $request->file('profile_photo');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_pics', $filename, 'public');
            $user->update(['profile_photo' => 'storage/profile_pics/' . $filename]);

            ActivityLogger::logActivity('profile_photo_uploaded', 'Staff uploaded profile photo', 'user', 'user', $user->id);
        }

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json(['success' => true, 'message' => 'Photo uploaded', 'user' => $user]);
        }

        return redirect()->route('staff.settings.index')->with('success', 'Photo uploaded successfully');
    }

    public function removePhoto(Request $request)
    {
        Gate::authorize('access-staff');
        $user = Auth::user();

        if (!$user->profile_photo) {
            return response()->json(['success' => false, 'message' => 'No photo to remove'], 400);
        }

        $photo = $user->profile_photo;
        if (strpos($photo, 'storage/') === 0) {
            $photo = substr($photo, 8);
        }
        if (Storage::disk('public')->exists($photo)) {
            Storage::disk('public')->delete($photo);
        }

        $user->update(['profile_photo' => null]);

        ActivityLogger::logActivity('profile_photo_removed', 'Staff removed profile photo', 'user', 'user', $user->id);

        return response()->json(['success' => true, 'message' => 'Profile photo removed']);
    }
}
