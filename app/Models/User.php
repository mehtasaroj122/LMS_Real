<?php

namespace App\Models;

use App\Notifications\CustomResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'phone',
        'gender',
        'date_of_birth',
        'address',
        'profile_photo',
        'status',
        'last_login_at',
        'otp',
        'otp_expires_at',
        'is_verified',
        'force_password_change',
        'password_reset_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'date_of_birth' => 'date',
            'is_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /* =======================
       ELOQUENT RELATIONSHIPS
       ======================= */

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    // Books issued by this user (librarian/staff)
    public function issuedBooks()
    {
        return $this->hasMany(IssuedBook::class, 'issued_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function hasCompletedRegistration(): bool
    {
        return filled($this->password);
    }

    public function requiresSelfRegistration(): bool
    {
        return in_array($this->role, ['staff', 'student'], true) && ! $this->hasCompletedRegistration();
    }

    public function getUnreadNotificationCount()
    {
        return $this->notifications()->unread()->count();
    }

    public function markAllNotificationsAsRead()
    {
        return $this->notifications()->unread()->update(['read_at' => now()]);
    }

    // ActivityLog relationship
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
