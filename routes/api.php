<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PartidaController;
use App\Http\Middleware\CheckUserStatus;

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


Route::middleware(['jwt.auth'])->group(function () { 
        Route::post('/score', [AuthController::class, 'score'])->name('score')->middleware(CheckUserStatus::class);
        Route::post('/createGame', [PartidaController::class, 'createGame'])->name('createGame');
        Route::post('/joinGame', [PartidaController::class, 'joinGame'])->name('joinGame');
        Route::post('/partidaCancelada', [PartidaController::class, 'partidaCancelada'])->name('partidaCancelada')->middleware(CheckUserStatus::class);
        Route::post('/partidaFinalizada', [PartidaController::class, 'partidaFinalizada'])->name('partidaFinalizada')->middleware(CheckUserStatus::class);
        Route::get('/index', [PartidaController::class, 'index'])->name('index')->middleware(CheckUserStatus::class);
        Route::post('/movimiento', [PartidaController::class, 'movimiento'])->name('movimiento')->middleware(CheckUserStatus::class);
        Route::get('/consultarCordenadas', [PartidaController::class, 'consultarCordenadas'])->name('consultarCordenadas')->middleware(CheckUserStatus::class);
        Route::get('/consultarCordenadasHit', [PartidaController::class, 'consultarCordenadasHit'])->name('consultarCordenadasHit')->middleware(CheckUserStatus::class);
        // Route::post('/partida', [PartidaController::class, 'partida'])->name('consultarCordenadas')->middleware(CheckUserStatus::class);

});
