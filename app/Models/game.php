<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class game extends Model
{
    use HasFactory;
    protected $table = 'games';
    protected $fillable = ['player1', 'player2', 'winner', 'status'];
    public function player1User()
    {
        return $this->belongsTo(User::class, 'player1');
    }

    public function player2User()
    {
        return $this->belongsTo(User::class, 'player2');
    }

    public function winnerUser()
    {
        return $this->belongsTo(User::class, 'winner');
    }
}
