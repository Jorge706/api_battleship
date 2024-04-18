<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PartidaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::get('/type', [AuthController::class, 'type'])->name('type');
Route::post('/register', [AuthController::class, 'store'])->name('register');
Route::get('/activating/{email}', [AuthController::class, 'activating'])->name('activating')->middleware('signed');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth', [AuthController::class, 'auth'])->name('auth');
//api para que el usuario pueda crear una partida

// Route::post('/createGame', [PartidaController::class, 'createGame'])->name('createGame');

Route::get('/type', [AuthController::class, 'type'])->name('type');



Route::post('/prueba', [AuthController::class, 'prueba']);


Route::middleware('jwt.auth')->group(function () { 
    Route::post('/createGame', [PartidaController::class, 'createGame'])->name('createGame');
    Route::post('/joinGame', [PartidaController::class, 'joinGame'])->name('joinGame');
    Route::post('/partidaCancelada', [PartidaController::class, 'partidaCancelada'])->name('partidaCancelada');
    Route::post('/partidaFinalizada', [PartidaController::class, 'partidaFinalizada'])->name('partidaFinalizada');
    Route::get('/index', [PartidaController::class, 'index'])->name('index');

    Route::post('/score', [AuthController::class, 'score'])->name('score');
    //consultar cordenadas
    Route::post('/consultarCordenadas', [PartidaController::class, 'consultarCordenadas'])->name('consultarCordenadas');
    

});
