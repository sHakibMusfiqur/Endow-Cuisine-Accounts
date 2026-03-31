<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo',
        'phone',
        'address',
        'last_login_at',
        'pending_email',
        'email_verification_token',
        'email_verification_sent_at',
        'email_verification_expires_at',
        'module_access',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the transactions created by this user.
     */
    public function transactions()
    {
        return $this->hasMany(DailyTransaction::class, 'created_by');
    }

    /**
     * Get the activity logs for this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Check if the user has the admin role.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user has the accountant role.
     *
     * @return bool
     */
    public function isAccountant()
    {
        return $this->hasRole('accountant');
    }

    /**
     * Check if the user is a restaurant accountant.
     * Restaurant accountants can only access restaurant transaction operations.
     *
     * @return bool
     */
    public function isRestaurantAccountant()
    {
        return $this->hasRole('accountant') && $this->module_access === 'restaurant';
    }

    /**
     * Check if the user is an inventory accountant.
     * Inventory accountants can only access inventory operations.
     *
     * @return bool
     */
    public function isInventoryAccountant()
    {
        return $this->hasRole('accountant') && $this->module_access === 'inventory';
    }

    /**
     * Check if the user can access the restaurant module.
     *
     * @return bool
     */
    public function canAccessRestaurantModule()
    {
        // Admins and Managers always have access
        if ($this->hasRole('admin') || $this->hasRole('manager')) {
            return true;
        }

        // Accountants can access if they have 'restaurant' or 'both' module access
        if ($this->hasRole('accountant')) {
            return $this->module_access === 'restaurant' || $this->module_access === 'both';
        }

        return false;
    }

    /**
     * Check if the user can access the inventory module.
     *
     * @return bool
     */
    public function canAccessInventoryModule()
    {
        // Admins and Managers always have access
        if ($this->hasRole('admin') || $this->hasRole('manager')) {
            return true;
        }

        // Accountants can access if they have 'inventory' or 'both' module access
        if ($this->hasRole('accountant')) {
            return $this->module_access === 'inventory' || $this->module_access === 'both';
        }

        return false;
    }

    /**
     * Check if the user has accountant module access restrictions.
     *
     * @return bool
     */
    public function hasAccountantModuleRestriction()
    {
        return $this->hasRole('accountant') && ($this->module_access === 'restaurant' || $this->module_access === 'inventory');
    }
}

