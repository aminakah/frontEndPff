<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use Exception;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        try {
            $user = new User();
            $user->prenom = $request->prenom;
            $user->nom = $request->nom;
            $user->adresse = $request->adresse;
            $user->telephone = $request->telephone;
            $user->email = $request->email;
            $user->password =Hash::make( $request->password);
            $user->save();

            $client = new Client();
            $client->client_id = $user->id; // Assurez-vous que le nom de la colonne est correct
            $client->save();

            return response()->json([
                'status_code'=>200,
                'status_message'=>'Utilisateur enregistré',
                'users'=>$user
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue lors de l\'inscription.'], 500);
        }
    }


    public function loginjk(LoginUserRequest $request)
    {
        // Tentez d'authentifier l'utilisateur
        if (auth()->attempt($request->only(['email', 'password']))) {
            $user = auth()->user();

            // Vérifiez si l'utilisateur est bloqué
            if ($user->status === 0) {
                // Utilisateur non bloqué, générer le token
                $token = $user->createToken('MA_CLE_SECRETE_VISIBLE_UNIQUEMENT_AU_BACKEND')->plainTextToken;

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Utilisateur connecté',
                    'user' => $user,
                    'token' => $token
                ]);
            } else {
                // Utilisateur bloqué, déconnecter et renvoyer un message d'erreur
                auth()->logout();

                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Votre compte a été bloqué. Veuillez contacter l\'administrateur ou le proprietaire de la pharmacie.'
                ]);
            }
        } else {
            // Si les informations ne correspondent à aucun utilisateur
            return response()->json([
                'status_code' => 403,
                'status_message' => 'Informations non valides. Vérifiez votre e-mail et votre mot de passe.'
            ]);
        }
    }
    public function login(LoginUserRequest $request){

        if (auth()->attempt($request->only(['email', 'password']))) {
            # code...
            $user = auth()->user();
            $token = $user->createToken('MA_CLE_SECRETE_VISIBLE_UNIQUEMENT_AU_BACKEND')->plainTextToken;
            return response()->json([
                'status_code'=>200,
                'status_message'=>'Utilisateur connecté ',
                'users'=>$user,
                'token'=>$token
            ]);
        }else {
            # Si les information ne correspondent a aucun utilisateur
            return response()->json([
                'status_code'=>403,
                'status_message'=>'Informations non valides. Vérifiez votre e-mail et votre mot de passe.
                ',
                // 'users'=>$user
            ]);
        }
   }
}
