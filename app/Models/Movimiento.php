<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model ;


class Movimiento extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'movimientos';
    protected $fillable = ['game_id', 'player_id', 'target_player_id', 'coordinate1', 'coordinate2', 'hits1', 'hits2'];
    // protected $casts = [
    //     'coordinate' => 'array',
    //     'coordinate2' => 'array',
    //     'hits1' => 'array',
    //     'hits2' => 'array',
    // ];
    
}
