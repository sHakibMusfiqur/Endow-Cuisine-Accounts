<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Mail\EmailChangeVerification;

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
                'email' => ['required', 'string', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:500'],
                'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            ]);

            // Remove profile_photo from validated data for now
            unset($validated['profile_photo']);

            $emailChanged = false;
            
            // Check if email has changed
            if ($validated['email'] !== $user->email) {
                // Check if email is already taken by another user
                $existingUser = \App\Models\User::where('email', $validated['email'])
                    ->where('id', '!=', $user->id)
                    ->first();
                
                if ($existingUser) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['email' => 'This email address is already in use.']);
                }

                // Generate verification token
                $token = Str::random(60);
                
                // Store pending email and token with 30 minute expiration
                $user->pending_email = $validated['email'];
                $user->email_verification_token = hash('sha256', $token);
                $user->email_verification_sent_at = now();
                $user->email_verification_expires_at = now()->addMinutes(30);
                
                $emailChanged = true;
            }

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

            // Send verification email if email changed
            if ($emailChanged) {
                $verificationUrl = url('/email-change/verify?token=' . $token);
                Mail::to($validated['email'])->send(new EmailChangeVerification($user, $validated['email'], $verificationUrl));
                
                return redirect()->route('profile.edit')
                    ->with('success', 'Profile updated! A verification link has been sent to ' . $validated['email'] . '. Please check your email to confirm the email change.');
            }

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

    /**
     * Verify the new email address.
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return redirect()->route('profile.edit')
                ->with('error', 'Invalid verification link.');
        }
        
        $hashedToken = hash('sha256', $token);
        
        $user = \App\Models\User::where('email_verification_token', $hashedToken)
            ->whereNotNull('pending_email')
            ->first();
        
        if (!$user) {
            return redirect()->route('profile.edit')
                ->with('error', 'Invalid or expired verification link.');
        }

        // Check if token is expired using the expires_at field
        if ($user->email_verification_expires_at && now()->isAfter($user->email_verification_expires_at)) {
            // Clear expired token
            $user->pending_email = null;
            $user->email_verification_token = null;
            $user->email_verification_sent_at = null;
            $user->email_verification_expires_at = null;
            $user->save();
            
            return redirect()->route('profile.edit')
                ->with('error', 'Verification link has expired. Please request a new one.');
        }

        // Update email address
        $user->email = $user->pending_email;
        $user->pending_email = null;
        $user->email_verification_token = null;
        $user->email_verification_sent_at = null;
        $user->email_verification_expires_at = null;
        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Email address updated successfully!');
    }

    /**
     * Cancel pending email change.
     */
    public function cancelEmailChange()
    {
        $user = Auth::user();
        
        if ($user->pending_email) {
            $user->pending_email = null;
            $user->email_verification_token = null;
            $user->email_verification_sent_at = null;
            $user->email_verification_expires_at = null;
            $user->save();
            
            return redirect()->route('profile.edit')
                ->with('success', 'Email change request cancelled.');
        }

        return redirect()->route('profile.edit');
    }
}
