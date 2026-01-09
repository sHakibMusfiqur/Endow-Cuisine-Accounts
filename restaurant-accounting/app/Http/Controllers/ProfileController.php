<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:500'],
                'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            ]);

            // Remove profile_photo from validated data for now
            unset($validated['profile_photo']);

            // Update basic fields
            $user->name = $validated['name'];
            $user->phone = $validated['phone'] ?? null;
            $user->address = $validated['address'] ?? null;

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                    Storage::disk('public')->delete($user->profile_photo);
                }

                // Store new photo
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $user->profile_photo = $path;
            }

            // Save user
            $user->save();

            return redirect()->route('profile.show')
                ->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for changing password.
     */
    public function editPassword()
    {
        return view('profile.change-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('profile.show')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Remove the user's profile photo.
     */
    public function destroyPhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
            $user->profile_photo = null;
            $user->save();
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profile photo removed successfully!');
    }
}
