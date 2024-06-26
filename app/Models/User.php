<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lastname',
        'phone',
        'password',
        'email',
        'time_verification_end',
        'email_verified_at',
        'active',
    ];

    // public function role()
    // {
    //     return $this->belongsTo(Role::class, 'rol'); // 'rol' es la columna que se usa como clave foránea en la tabla 'users'
    // }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
