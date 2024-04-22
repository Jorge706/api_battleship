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
        
        'phone',
        'password',
        'email',
        'time_verification_end',
        'email_verified_at',
        'active',
        'games_won',
        'games_lost',
        'partidas',
        'partida_actual',
        'status',
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
    public function gameResults()
    {
        $games = Game::where('player1', $this->id)->orWhere('player2', $this->id)->get();

        $results = $games->map(function ($game) {
            $opponent = $game->player1 == $this->id ? $game->player2User : $game->player1User;
            $result = $game->winner == $this->id ? 'ganado' : 'perdido';
            return ['resultado' => $result, 'contra' => $opponent->name];
        });

        return $results;
    }
    public function partidaActual()
    {
        return $this->belongsTo('App\Game', 'partida_actual');
    }
}
