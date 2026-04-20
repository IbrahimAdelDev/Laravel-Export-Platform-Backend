<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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

    // Company that the user is associated with (if any)
    public function companies()
    {
        return $this->belongsToMany(Company::class)
                    ->withPivot('role', 'status') // include role and status from the pivot table
                    ->withTimestamps();
    }

    // Phones associated with the user (polymorphic relationship)
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    // Products that the user is interested in
    public function interests()
    {
        return $this->belongsToMany(Product::class, 'user_interests');
    }

    public function actions()
    {
        return $this->morphMany(ActivityLog::class, 'causer');
    }
}
