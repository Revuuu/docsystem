<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\HasRolesAndPermissions;
use Laratrust\Contracts\LaratrustUser;
use App\Notifications\ResetPasswordNotification;
use App\Models\LoginOtp;

class User extends Authenticatable implements LaratrustUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'gender',
        'signature_path',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function loginOtps()
    {
        return $this->hasMany(LoginOtp::class);
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(
            new ResetPasswordNotification($token)
        );
    }
    public function assignedSignatureBlocks(): HasMany
    {
        return $this->hasMany(
            DocumentSignatureBlock::class,
            'assigned_user_id'
        );
    }

    public function signedSignatureBlocks(): HasMany
    {
        return $this->hasMany(
            DocumentSignatureBlock::class,
            'signed_by_user_id'
        );
    }
}
