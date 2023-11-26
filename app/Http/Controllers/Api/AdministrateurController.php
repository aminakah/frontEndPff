<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\Region;
use App\Models\Quartier;
use App\Models\Departement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RegisterUserRequest;

class AdministrateurController extends Controller
{
    //

    public function ajouterProprietaire(RegisterUserRequest $request)
    {
        try {
            // Vérifiez d'abord si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez ensuite si l'utilisateur a le profil "propriétaire"
                if ($user->profile === 'admin') {
                    $user = new User();
                    $user->prenom = $request->prenom;
                    $user->nom = $request->nom;
                    $user->email = $request->email;
                    $user->adresse = $request->adresse;
                    $user->telephone = $request->telephone;
                    $user->profile = 'proprietaire';
                    $user->password = $request->password;
                    $user->save();

                    $proprietaire = new Proprietaire();
                    $proprietaire->proprietaire_id = $user->id;
                    $proprietaire->save();
                    return response()->json([
                        'status_code'=>200,
                        'status_message'=>'proprietaire enregistré',
                        'users'=>$user
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas le profil "admin" pour ajouter une pharmacie.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour ajouter une proprietaire.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'ajout de la proprietaire.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ajoutRegion(Request $request)
    {
        try {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->profile === 'admin') {
                    $validator = Validator::make($request->all(), [
                        'nom' => 'required|string|unique:regions,nom',
                    ]);

                    if ($validator->fails()) {
                        return response()->json(['error' => $validator->errors()], 400);
                    }

                    $region = Region::create([
                        'nom' => $request->nom,
                    ]);

                    return response()->json(['message' => 'La région a été ajoutée avec succès.', 'data' => $region], 201);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour effectuer cette action.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté en tant qu\'administrateur pour effectuer cette action.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'ajout de la région.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function ajoutDepartement(Request $request)
    {
        try {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->profile === 'admin') {
                    $validator = Validator::make($request->all(), [
                        'nom' => 'required|string|unique:departements,nom',
                        'region_id' => 'required|exists:regions,id',
                    ]);

                    if ($validator->fails()) {
                        return response()->json(['error' => $validator->errors()], 400);
                    }

                    $departement = Departement::create([
                        'nom' => $request->nom,
                        'region_id' => $request->region_id,
                    ]);

                    return response()->json(['message' => 'Le département a été ajouté avec succès.', 'data' => $departement], 201);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour effectuer cette action.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté en tant qu\'administrateur pour effectuer cette action.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'ajout du département.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ajoutQuartier(Request $request)
    {
        try {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->profile === 'admin') {
                    $validator = Validator::make($request->all(), [
                        'nom' => 'required|string|unique:quartiers,nom',
                        'departement_id' => 'required|exists:departements,id',
                    ]);

                    if ($validator->fails()) {
                        return response()->json(['error' => $validator->errors()], 400);
                    }

                    $quartier = Quartier::create([
                        'nom' => $request->nom,
                        'departement_id' => $request->departement_id,
                    ]);

                    return response()->json(['message' => 'Le quartier a été ajouté avec succès.', 'data' => $quartier], 201);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour effectuer cette action.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté en tant qu\'administrateur pour effectuer cette action.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'ajout du quartier.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bloquerDebloquerProprietaire($proprietaireId)
    {
        try {
            // Vérifiez si l'utilisateur est authentifié et a le profil "admin"
            if (auth()->check() && auth()->user()->profile === 'admin') {
                $proprietaire = User::find($proprietaireId);

                if ($proprietaire) {
                    // Vérifiez si le propriétaire a le profil "proprietaire"
                    if ($proprietaire->profile === 'proprietaire') {
                        // Basculez le statut "bloqué"
                        $proprietaire->update([
                            'status' => !$proprietaire->status,
                        ]);

                        $statusMessage = $proprietaire->status ? 'Proprietaire bloqué.' : 'Proprietaire débloqué.';

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => $statusMessage,
                            'proprietaire' => $proprietaire,
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Le profil de l\'utilisateur n\'est pas "proprietaire". Impossible de bloquer/débloquer.',
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 404,
                        'status_message' => 'Proprietaire non trouvé.',
                    ], 404);
                }
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas le profil "admin" pour bloquer/débloquer un proprietaire.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors du blocage/déblocage du proprietaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
}

    public function listeProprietaires()
    {
        try {
            // Vérifiez d'abord si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Utilisez une constante pour le profil "admin"
                if ($user->profile === 'admin') {
                    // Utilisez Eloquent pour simplifier la requête
                    $proprietaires = User::where('profile', 'proprietaire')->get();

                    // Vérifiez si des propriétaires ont été trouvés
                    if ($proprietaires->isEmpty()) {
                        return response()->json([
                            'status_code' => 404,
                            'status_message' => 'Aucun propriétaire trouvé.',
                        ], 404);
                    }

                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'Liste des propriétaires récupérée avec succès.',
                        'proprietaires' => $proprietaires
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas le profil "admin" pour lister les propriétaires.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour lister les propriétaires.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération de la liste des propriétaires.',
                'error' => $e->getMessage(),
            ], 500);
    }
}



}
