<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use App\Models\game;
use App\Models\Movimiento;  

class PartidaController extends Controller
{
    public function createGame(Request $request)
    { 
        //si el usuario ya tiene una partida en curso (user), no puede crear otra
        $user = auth()->user();
        if ($user->status == 'user'|| $user->status == 'guest') {
            return response()->json([
                "mensaje" => "Ya tienes una partida en curso."
            ], 400);
        }
        $user->status = 'user';
        $user->save();

        $partida = new game();
        $partida->player1 = $user->id;
        $partida->save();
        //quiero enviar el id de la partida

        return response()->json([
            "mensaje" => "Partida creada",
            "id" => $partida->id
        ], 201);

    }


    public function partidaCancelada (Request $request)
    {
        $this->validate($request, [
            'id' => 'required' //id de la partida
        ]);
        $partida = game::where('id', $request->id)->first();
        $partida->status = 'cancelled';
        $partida->save();

        return response()->json([
            "mensaje" => "Partida Cancelada",
            // "data"  => collect($partida)->except(['id', 'created_at', 'updated_at'])
        ], 202);
    }

    public function joinGame(Request $request)
    {
                //si el usuario ya tiene una partida en curso (guest), no puede unirse a otra
        $user = auth()->user();
        if ($user->status == 'guest' || $user->status == 'user') {
            return response()->json([
                "mensaje" => "Ya tienes una partida en curso."
            ], 400);
        }
        
        $this->validate($request, [
            'id' => 'required' //id de la partida
        ]);
        //si el id de la partida existe 
        $partida = game::find($request->id);

        // Verificar si la partida existe
        if (!$partida) {
            return response()->json([
                "mensaje" => "La partida con el ID proporcionado no existe."
            ], 404);
        }
        $user = auth()->user();
        $user->status = 'guest';
        $user->save();

        $partida = game::where('id', $request->id)->first();
        $partida->player2 = $user->id;
        $partida->status = 'in_progress';
        $partida->save();

        //quiero que despues de un tablero de 8x5 me de 15 posiciones aleatorias por jugador lo mande a mongo 
        $player1Positions = $this->generateRandomPositions();
        $player2Positions = $this->generateRandomPositions();

        //Y despues quiero que me mande a mongo las posiciones de los barcos de cada jugador
       // Crear un nuevo movimiento para el jugador 1
            $movimiento1 = new Movimiento();
            $movimiento1->game_id = $partida->id; // Suponiendo que tienes el objeto $partida con la partida actual
            $movimiento1->player_id = $user->id;
            $movimiento1->coordinate = $player1Positions[0]; // Por ejemplo, toma la primera posición aleatoria
            $movimiento1->save();

            $movimiento1Id = $movimiento1->_id;

            // Crear un nuevo movimiento para el jugador 2
            $movimiento2 = new Movimiento();
            $movimiento2->game_id = $partida->id; // Suponiendo que tienes el objeto $partida con la partida actual
            $movimiento2->player_id = $partida->player2; // Suponiendo que ya guardaste el ID del jugador 2 en la partida
            $movimiento2->coordinate = $player2Positions[0]; // Por ejemplo, toma la primera posición aleatoria
            $movimiento2->save();

            $movimiento2Id = $movimiento2->_id;
            //y despues que lo guarde en 

            //regresame el id de la partida


            return response()->json([
                "mensaje" => "Movimientos guardados en MongoDB para ambos jugadores.",
                "movimiento_jugador1_id" => $movimiento1Id,
                "movimiento_jugador2_id" => $movimiento2Id,
            ], 200);
    }

    public function index()
    { //enviar todas las partidas donde  las partidas que estan en pending, enviar el nombre del jugador y la id de la partida
        $partidas = game::where('status', 'pending')->get();
        
        $partidas = $partidas->map(function ($partida) {
            $player1 = User::find($partida->player1);
            return [
                "id" => $partida->id,
                "player1" => $player1->name
            ];
        });

        return response()->json([
            "partidas" => $partidas
        ], 200);
        
    }


    public function generateRandomPositions()
    {
        $positions = [];
    
        $letters = ['A', 'B', 'C', 'D', 'E'];
        
        while (count($positions) < 15) {
            $row = $letters[mt_rand(0, 5 - 1)];
            $col = mt_rand(1, 8);
    
            // Asegurarse de que la posición no esté repetida
            $position = "$row$col";
            if (!in_array($position, $positions)) {
                $positions[] = $position;
            }
        }
    
        return $positions;
    }

    public function finishGame(Request $request)
    {
        $this->validate($request, [
            'id' => 'required', //id de la partida
            'winner' => 'required' //id del ganador
        ]);
        $partida = game::where('id', $request->id)->first();
        $partida->winner = $request->winner;
        $partida->status = 'finished';
        $partida->save();

        return response()->json([
            "mensaje"   => "Partida Finalizada",
            // "data"  => collect($partida)->except(['id', 'created_at', 'updated_at'])
        ], 202);
    }

}
