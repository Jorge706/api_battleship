<?php

use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// use Jenssegers\Mongodb\Schema\Blueprint;
use MongoDB\Laravel\Schema\Blueprint ;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mongodb')->create('movimientos', function (Blueprint $collection) {
            $collection->bigIncrements('id');
            $collection->string('game_id');
            $collection->string('player_id');
            $collection->string('target_player_id')->nullable();
            $collection->string('coordinate');
            $collection->string('hit'); //las cordenadas que cada jugador le ha atinado al otro
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('barco');
    }
};
