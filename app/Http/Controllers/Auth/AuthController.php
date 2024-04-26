<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
// use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ActivateUser;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller

{
    public function score(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $results = $user->gameResults();
        return response()->json($results, 200);
    }   
 
    public function prueba()
    {
        // event(new \App\Events\MyEvent('hello world'));
    }

    //para hacerce un usuario (registrarse)
    public function store(Request $request)
    {


        $validator = Validator::make($request->all(),[
            'name' => 'required|max:20|min:3',
            'phone' => 'required|numeric|digits:10', // para que sean 10 digitos es con 
            'password' => 'required|min:8|confirmed',
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        $user = new User();

        $user->name = $request->name;
        // $model->lastname = $this->lastname;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->email = $request->email;
        $user->time_verification_end = now()->addMinutes(10);
        $user->save();

        $signedUrl = URL::temporarySignedRoute( //porque 
            'activating', now()->addMinutes(10), ['email' => $user->email]
        );

        Mail::to($user)->send(new ActivateUser($user, $signedUrl));
        return response()->json([
            "mensaje"   => "Correo Enviado",
            "data"  => collect($user)->except(['id', 'created_at', 'updated_at'])
        ], 202);
    }

    public function activating(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user->email_verified_at) {
            return response()->json(['error' => 'Token already validated'], 400);
        }
        $user->email_verified_at = now();
        $user->active = true;
        $user->save();
 
        return redirect('http://192.168.124.201:4200/ingreso');
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }
        $credentials = request(['email', 'password']);

        if (!auth()->attempt($credentials)) { 
            return response()->json(["mensaje" => 'Credenciales invalidas, o cuenta inexistente'], 200);
        }
        $user = User::find(auth()->user()->id);
        if ($user->email_verified_at == null) 
        { 
            if (auth()->user()->time_verification_end < now()) 
            { 

                $user = auth()->user();
                $user->time_verification_end =  now()->addMinutes(10);
                if ($user instanceof User) {
                    $user->save(); 
                }
                $signedUrl = URL::temporarySignedRoute( //porque 
                    'activating', now()->addMinutes(10), ['email' => $user->email]
                );
                Mail::to($user)->send(new ActivateUser($user, $signedUrl));
                
                return response()->json(['mensaje' => 'User not activated, check your email, we have just send you a new one'], 200);
            }
            return response()->json(["mensaje" => 'User not activated, Check your Email'], 202);
        }
            $code = rand(100000, 999999);
            $user->verification_code = $code;
            $user->save();
            Mail::send('emails.verification', ['code' => $code, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Código de verificación');
            });
        return response()->json(['mensaje' => 'codigo de verificacion enviado'], 202);
    }


    public function auth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'code' => 'required|numeric|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }
        //checamos que email y contraseña sean correctos
        $credentials = request(['email', 'password']);
        if (!auth()->attempt($credentials)) {
            return response()->json(['mensaje' => 'Credenciales invalidas'], 200);
        }
        $user = User::find(auth()->user()->id);
        if ($user->email_verified_at == null) {
            return response()->json(['mensaje' => 'Usuario no activado'], 200);
        }
        if ($user->verification_code != $request->code) {
            return response()->json(['mensaje' => 'Codigo de verificacion incorrecto'], 200);
        }
        $token = JWTAuth::fromUser($user);
        $user->save();

        return response()->json(['token' => $token], 202);
    }

}
