<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use App\Models\game;
use App\Models\Movimiento;  
use Illuminate\Support\Facades\DB;

class PartidaController extends Controller
{

    public function movimiento(Request $request)
    {
        $this->validate($request, [
            'coordinate' => 'required' //coordenada
        ]);
        //si el usuario no tiene una partida en curso
        $user = User::find(auth()->user()->id);
        if ($user->status == 'inactive') {
            return response()->json([
                "mensaje" => "No tienes una partida en curso."
            ], 400);
        }

        // $movimiento = Movimiento::find( auth()->user()->partida_actual );
        $movimiento = DB::connection('mongodb')->collection('movimientos')->where('_id', auth()->user()->partida_actual)->first();
        return response()->json([
            "mensaje" => "Movimiento guardado",
            "data" => $movimiento
        ], 200);
        //si el usuario le atina a una coordenada dentro de coordenadas
        // if (in_array($request->coordinate, $movimiento->['coordinate'])) {
        //     $movimiento->hit_coordinates[] = $request->coordinate;
        //     $movimiento->save();
        // }
        //y si ya le atino a todos los barcos del otro jugador
        if (count($movimiento->hit_coordinates) == 15) {
            $partida = game::find($movimiento->game_id);
            $partida->status = 'finished';
            $partida->winner = $movimiento->player_id;
            $partida->save();

            return response()->json([
                "mensaje" => "Partida Finalizada ha ganado ",
                "ganador" => $movimiento->player_id
            ], 200);
        }
        return response()->json([
            "mensaje" => "Movimiento guardado",
            "data" => $movimiento
        ], 200);
    }
    public function consultarCordenadas()
    {
        $movimiento = Movimiento::find(auth()->user()->partida_actual);

        //quiero ver si eres guest o user
        return response()->json([
            "mensaje" => "Movimiento encontrado",
            "data" => $movimiento,
            "posicion"=> auth()->user()->status,
            "id_partida"=> auth()->user()->partida_actual
        ], 200);
    }




    public function createGame(Request $request)
    { 
        //si el usuario ya tiene una partida en curso (user), no puede crear otra
        $user = User::find(auth()->user()->id);
        if ($user->status != 'inactive' || $user->partida_actual != null) {
            return response()->json([
                "mensaje" => "Ya tienes una partida en curso."
            ], 400);
        }
        

        $partida = new game();
        $partida->player1 = $user->id;
        $partida->save();

        $user->status = 'user';
        $user->partida_actual = $partida->id;
        $user->save();

        event(new \App\Events\MyEvent('hola mundo'));

        return response()->json([
            "mensaje" => "Partida creada",
            "data" => [
                "id" => $partida->id,
                "player1" => $user->name
            ]

        ], 201);

    }


    public function partidaCancelada (Request $request)
    {
        $user = User::find(auth()->user()->id);
        $idpartida = $user->partida_actual;
        if (game::where('id', $idpartida)->exists() ) //esto es si hay partida, pero aun no sabemos si ya tiene oponente
        {
            $partida = game::where('id', $idpartida)->first();
                    //preguntamos si la partida esta empezada o no 
            if ($partida && $partida->status == 'pending')
            {
                // $partida = game::where('id', $request->id)->first();
                $partida->status = 'cancelled';
                $partida->save();
                $user->status = 'inactive';
                $user->partida_actual = null;
                $user->save();
                
            }else if ($partida->status == 'in_progress')
            {//si ya empezo la partida, gana el otro jugador
                 
                if ($partida->player1 == auth()->user()->id )
                {
                $user->status = 'inactive';
                $user->partida_actual = null;
                $user->save();

                $user2 = User::find($partida->player2);
                $user2->status = 'inactive';
                $user2->partida_actual = null;
                $user2->save();

                $partida->winner = $partida->player2;


                return response()->json([
                    "mensaje" => "Partida Cancelada",
                ], 202);
                }else if ($partida->player2 == auth()->user()->id)
                {
                    
                    //hacemos que el usuario vuelva a estar en status de guest y que el player2 tambien vuelva a estar en status de guest
                    $user = User::find(auth()->user()->id);
                    $user->status = 'inactive';
                    $user->partida_actual = null;
                    $user->save();
            
                    $user2 = User::find($partida->player1);
                    $user2->status = 'inactive';
                    $user2->partida_actual = null;
                    $user2->save();

                    $partida->winner = $partida->player1;

            
                    return response()->json([
                        "mensaje" => "Partida Cancelada",
                        // "data"  => collect($partida)->except(['id', 'created_at', 'updated_at'])
                    ], 202);
                }
                $partida->status = 'cancelled';
                $partida->save();

            }else if ($partida->status == 'finished'|| $partida->status == 'cancelled')
            {
                return response()->json([
                    "mensaje" => "La partida ya ha finalizado."
                ], 400);
            }
        

        }else
        {
            return response()->json([
                "mensaje" => "La partida con el ID proporcionado no existe."
            ], 404);
        }
        
    }


    public function joinGame(Request $request)
    {
                //si el usuario ya tiene una partida en curso (guest), no puede unirse a otra
        $user2 = auth()->user();
        if ($user2->status == 'guest' || $user2->status == 'user') {
            return response()->json([
                "mensaje" => "Ya tienes una partida en curso."
            ], 400);
        }
        
        $this->validate($request, [
            'id' => 'required' //id de la partida
        ]);
        //si la partida existe 
        $partida = game::find($request->id);

        // Verificar si la partida existe
        if (!$partida || $partida->status != 'pending') {
            return response()->json([
                "mensaje" => "La partida con el ID proporcionado no existe."
            ], 404);
        }
        $user2 = User::find($user2->id);

        $partida->player2 = $user2->id;
        $partida->status = 'in_progress';
        $partida->save();
        

        //quiero que despues de un tablero de 8x5 me de 15 posiciones aleatorias por jugador lo mande a mongo 
        $player1Positions = $this->generateRandomPositions();
        $player2Positions = $this->generateRandomPositions();
       
            $movimiento1 = new Movimiento();
            $movimiento1->game_id = $partida->id; // Suponiendo que tienes el objeto $partida con la partida actual
            $movimiento1->player_id = $partida->player1; 
            $movimiento1->coordinate = $player1Positions; 
            $movimiento1->save();

            $movimiento1Id = $movimiento1->_id;

            $user = User::find($partida->player1);
            $user->partida_actual = $movimiento1Id;
            $user->save();


            // Crear un nuevo movimiento para el jugador guest
            $movimiento2 = new Movimiento();
            $movimiento2->game_id = $partida->id;
            $movimiento2->player_id =  $partida->player2;
            $movimiento2->coordinate = $player2Positions; 
            $movimiento2->save();

            
            $movimiento2Id = $movimiento2->_id;

        //          aqui guardo cambio el estado del guest
            // $user = User::find(auth()->user()->id);
            $user2->status = 'guest';
            $user2->partida_actual = $movimiento2Id;
            $user2->save();    
            //y guardamos el id del que se une a la partida y el stado de la partida en in_progress
            

            $user2->partida_actual = $movimiento2Id;
            $user2->save();

            //el otro se lo va a enviar al otro jugador por medio de websocket 

            // event(new \App\Events\MyEvent('hola mundo'));

            event(new \App\Events\UserJoinedGameEvent($user2, $partida)); //enviar el nombre del jugador que creo la partiday la id de la partida

            return response()->json([
                "mensaje" => "Movimientos guardados en MongoDB para ambos jugadores.",
                "movimiento_jugador1_id" => $movimiento2Id,
                // "movimiento_jugador2_id" => $movimiento2Id,
                // "player1Positions" => $player2Positions,
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
            "partidas" => $partidas,
            // "prueba" => 'hola john'
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
