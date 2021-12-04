<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    public function eptools()
    {
        return $this->hasMany(Eptool::class);
    }
    public function insurances()
    {
        return $this->hasMany(Insurance::class);
    }
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
    public function galleries()
    {
        return $this->hasOne(Gallery::class);
    }
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'imagePath',
        'birthDate',
        'area',
        'bankName',
        'rocket',
        'bkash',
        'nogod',
        'phonenumber',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}
