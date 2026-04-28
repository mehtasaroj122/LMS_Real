<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $originalEmail = $user->email;
        $changes = [];

        $user->fill($request->validated());

        // Track changes after applying the validated payload.
        if ($user->isDirty('email')) {
            $changes['email'] = $originalEmail;
            $user->email_verified_at = null;
        }
        if ($user->isDirty('name')) {
            $changes['name'] = $user->name;
        }
        if ($user->isDirty('phone')) {
            $changes['phone'] = $user->phone;
        }

        $user->save();

        // Notify user of profile update
        Notification::notify(
            user: $user,
            type: 'account.profile_updated',
            title: 'Profile Information Updated',
            message: 'Your profile information was updated on ' . now()->format('M d, Y h:i A'),
            data: ['changes' => $changes, 'ip' => request()->ip()],
            relatedModel: 'User',
            relatedId: $user->id
        );

        // Notify of email change separately if applicable
        if (isset($changes['email'])) {
            Notification::notify(
                user: $user,
                type: 'account.email_changed',
                title: 'Email Address Changed',
                message: 'Your email address was changed to ' . $user->email,
                data: ['old_email' => $changes['email'], 'new_email' => $user->email],
                relatedModel: 'User',
                relatedId: $user->id
            );
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
