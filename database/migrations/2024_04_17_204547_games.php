<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player1');
            $table->unsignedBigInteger('player2')->nullable();
            $table->unsignedBigInteger('winner')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'finished'])->default('pending');
            $table->timestamps();

            $table->foreign('player1')->references('id')->on('users');
            $table->foreign('player2')->references('id')->on('users');
            $table->foreign('winner')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
