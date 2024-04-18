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
 
    public function type()
    {
        //aqui me regresa el tipo de usuario que es
        return response()->json(auth()->user()->rol);
    }
    public function prueba()
    {
        event(new \App\Events\MyEvent('hello world'));
    }

    //para hacerce un usuario (registrarse)
    public function store(Request $request)
    {
        $content = new UserValidation();
        $error = $content->checkSignUp($request); 
         if ($error != null) {
        return response()->json(["mensaje" => $error], 200);
        }
        $user = new User();
        $content->signUp($user);
        $content->sendURL($user);

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
        // $user->save();
        // return response()->json([
        //     "msg"   => "activado",
        //     "data"  => collect($user)->except(['id',  'time_verification_end', 'created_at', 'updated_at'])
        // ], 202);
        // $token = JWTAuth::fromUser($user);
        // $user->remember_token = $token;
        $user->save();

        return redirect('http://localhost:4200/ingreso');
    }


    public function login(Request $request)
    {//en el login se le tienen que enviar 6 digitos al hazar al correo del usuario
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }
        $credentials = request(['email', 'password']);

        if (!auth()->attempt($credentials)) { // el attempt es para verificar si el usuario existe
            return response()->json(["mensaje" => 'Credenciales invalidas, o cuenta inexistente'], 200);
        }
        $content = new UserValidation();
        $user =auth()->user();
        if ($user->email_verified_at == null) // si el usuario no esta activado, pero si existe el usuario
        { 
            if (auth()->user()->time_verification_end < now()) //si ya se acabo el token de verificacion
            { 

                $user = auth()->user();
                $user->time_verification_end =  $content->expirationTime();
                if ($user instanceof User) {
                    $user->save(); // Guarda los cambios en la base de datos
                }
                $content->sendURL($user);
                return response()->json(['mensaje' => 'User not activated, check your email, we have just send you a new one'], 200);
            }// si aun no se acaba
            return response()->json(["mensaje" => 'User not activated, Check your Email'], 202);
        }


        $content->sendVerificationCode($user);

        return response()->json(['mensaje' => 'codigo de verificacion enviado'], 202);

        
    }


    public function auth(Request $request)
    {
        //aqui voy a recibir el email,contraseña y el codigo de verificacion
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
        //checamos que el usuario este activado 
        $user = auth()->user();
        if ($user->email_verified_at == null) {
            return response()->json(['mensaje' => 'Usuario no activado'], 200);
        }
        //checamos que el codigo de verificacion sea correcto
        if ($user->verification_code != $request->code) {
            return response()->json(['mensaje' => 'Codigo de verificacion incorrecto'], 200);
        }

        // $token = JWTAuth::fromUser($user);
        // $customClaims = ['exp' => Carbon::now()->addMinutes(120)->timestamp]; //para que dure 120 minuto el token, despues de eso 
        // $token = JWTAuth::claims($customClaims)->fromUser($user);
        $token = JWTAuth::fromUser($user);
        // $user->setRememberToken($token);
        $user->save();

        return response()->json(['token' => $token], 202);
    }


// 56139733, numero de reporte telmex



        // $token= JWTAuth::fromUser($user);


        // //si se guardo correctamente
        
        // if ($user->id) {
        //     return response()->json(["Correctamente_Registrado" => "hola","Datos:" => $user, "Token:" => $token], 201);
        //     // se envia el correo con el token
        // } else {
        //     return response()->json(["Error" => "Error al guardar el usuario"], 500);
        // }
// 
    // } 
}


class UserValidation {
    protected $name, $lastname, $email, $phone, $password;

    public function sendVerificationCode(User $user)
    {
    // Genera un código de 6 dígitos al azar
    $code = rand(100000, 999999);

    // Guarda el código en la base de datos
    $user->verification_code = $code;
    $user->save();

    // Envía el código al correo del usuario
    Mail::send('emails.verification', ['code' => $code, 'user' => $user], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Código de verificación');
    });
    }

    public function checkSignUp(Request $request) {

        $validator = Validator::make($request->all(),[
            'name' => 'required|max:20|min:3',
            'phone' => 'required|numeric|digits:10', // para que sean 10 digitos es con 
            'password' => 'required|min:8|confirmed',
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }
        else
        {
            //constructor 
            $this->name = $request->name;
            // $this->lastname = $request->lastname;
            $this->phone = $request->phone;
            $this->password = $request->password;
            $this->email = $request->email;
        }

    }

    public function signUp(&$model) {
        $model->name = $this->name;
        // $model->lastname = $this->lastname;
        $model->phone = $this->phone;
        $model->password = Hash::make($this->password);
        $model->email = $this->email;
        $model->time_verification_end = $this->expirationTime();
        $model->save();
    }

    public function checkSignIn(Request $request) {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }
        else
        {
            $this->email = $request->email;
            $this->password = $request->password;
        }
    }

    public function signIn(&$model) {
        $model->email = $this->email;
        $model->password = $this->password;
    }

    public function sendURL(&$model) {
        // $token = JWTAuth::fromUser($model);
        $signedUrl = URL::temporarySignedRoute( //porque 
            'activating', $this->expirationTime(), ['email' => $model->email]
        );
        Mail::to($model)->send(new ActivateUser($model, $signedUrl));
        
    }

    public function expirationTime() {
        return now()->addMinutes(10); // para que dure 1 minuto el token 
    }


}