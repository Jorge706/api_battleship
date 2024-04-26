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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 10)->nullable();
            $table->string('password');
            $table->string('email')->unique();
            $table->timestamp('time_verification_end')->nullable(); 
            $table->timestamp('email_verified_at')->nullable();
            $table->string('active')->default(false); 
            // $table->integer('games_won')->default(0); 
            // $table->integer('games_lost')->default(0); 
            // $table->boolean('partidas')->default(false);
            // $table->string('partida_actual')->nullable();
            $table->enum('status', ['user', 'guest','inactive'])->default('inactive');

            // $table->string('rol')->default('user'); 
            $table->integer('verification_code')->nullable(); //este campo es para el token de verificacion)
            // $table->string('remember_token', 2000)->nullable();
            $table->timestamps();



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
