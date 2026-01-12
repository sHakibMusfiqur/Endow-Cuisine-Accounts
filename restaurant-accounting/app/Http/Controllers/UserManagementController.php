<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $this->authorize('manage users');

        $users = User::with('roles')->latest()->paginate(15);

        ActivityLog::log('view', 'Viewed user management page', 'users');

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('manage users');

        $roles = Role::all();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('manage users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,accountant,manager',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        ActivityLog::log('create', "Created new user: {$user->name} ({$user->email}) with role: {$validated['role']}", 'users', [
            'user_id' => $user->id,
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('manage users');

        $user->load('roles');
        $activityLogs = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        ActivityLog::log('view', "Viewed user details: {$user->name}", 'users', ['user_id' => $user->id]);

        return view('users.show', compact('user', 'activityLogs'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('manage users');

        // Prevent editing own account through this interface
        if ($user->id === auth()->id()) {
            return redirect()->route('profile.edit')
                ->with('info', 'Please use Profile Settings to edit your own account.');
        }

        $user->load('roles');
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('manage users');

        // Prevent editing own account through this interface
        if ($user->id === auth()->id()) {
            return redirect()->route('profile.edit')
                ->with('info', 'Please use Profile Settings to edit your own account.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,accountant,manager',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $changes = [];

        if ($user->name !== $validated['name']) {
            $changes['name'] = ['from' => $user->name, 'to' => $validated['name']];
        }

        if ($user->email !== $validated['email']) {
            $changes['email'] = ['from' => $user->email, 'to' => $validated['email']];
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($validated['password'])]);
            $changes['password'] = 'updated';
        }

        $currentRole = $user->roles->first()?->name;
        if ($currentRole !== $validated['role']) {
            $user->syncRoles([$validated['role']]);
            $changes['role'] = ['from' => $currentRole, 'to' => $validated['role']];
        }

        ActivityLog::log('update', "Updated user: {$user->name}", 'users', [
            'user_id' => $user->id,
            'changes' => $changes,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('manage users');

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;
        $userEmail = $user->email;

        ActivityLog::log('delete', "Deleted user: {$userName} ({$userEmail})", 'users', [
            'user_id' => $user->id,
            'role' => $user->roles->first()?->name,
        ]);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Display activity logs for all users.
     */
    public function activityLogs()
    {
        $this->authorize('manage users');

        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return view('users.activity-logs', compact('logs'));
    }
}
