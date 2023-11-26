<?php

namespace App\Http\Controllers\Api;

use App\Models\Pharmacie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AjouterPharmacieRequest;
use App\Http\Requests\ModifierPharmacieRequest;
use App\Models\Horaire;
use App\Models\PharmacieAgent;
use App\Models\Proprietaire;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class PharmacieController extends Controller
{
    
    public function ajouterPharmacie(AjouterPharmacieRequest $request)
    {
        try {
            // Vérifiez d'abord si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez ensuite si l'utilisateur a le profil "propriétaire"
                if ($user->profile === 'proprietaire') {
                    $pharmacie = new Pharmacie();
                    $pharmacie->nom = $request->nom;
                    if ($request->hasFile('photo')) {
                        $imagePath = $request->file('photo');
                        $extension = $imagePath->getClientOriginalExtension();
                        $filename = time() . '.' . $extension;
                        $imagePath->move('images/', $filename);
                        $pharmacie->photo = $filename;
                    }
                    $pharmacie->adresse = $request->adresse;
                    $pharmacie->telephone = $request->telephone;
                    $pharmacie->fax = $request->fax;
                    $pharmacie->latitude = $request->latitude;
                    $pharmacie->longitude = $request->longitude;
                    $pharmacie->proprietaire_id = $user->id;
                    $pharmacie->quartier_id = $request->quartier_id;
                    $pharmacie->save();
                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'La pharmacie a été ajoutée avec succès.',
                        'data' => $pharmacie
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas le profil "propriétaire" pour ajouter une pharmacie.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour ajouter une pharmacie.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'ajout de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function ajouterPharmaciejkl(AjouterPharmacieRequest $request)
    {
        try {
            // Vérifiez d'abord si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez ensuite si l'utilisateur a le profil "propriétaire"
                if ($user->profile === 'proprietaire') {
                    $pharmacie = new Pharmacie();
                    $pharmacie->nom = $request->nom;
                    dd($request);
                    if ($request->hasFile('photo')) {
                        $imagePath = $request->file('photo');
                        $extension = $imagePath->getClientOriginalExtension();
                        $filename = time() . '.' . $extension;
                        $imagePath->move('images/', $filename);
                        $pharmacie->photo = $filename;
                    }
                    $pharmacie->adresse = $request->adresse;
                    $pharmacie->telephone = $request->telephone;
                    $pharmacie->fax = $request->fax;
                    $pharmacie->latitude = $request->latitude;
                    $pharmacie->longitude = $request->longitude;
                    $pharmacie->proprietaire_id = $user->id;
                    $pharmacie->quartier_id = $request->quartier_id;
                    $pharmacie->save();
                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'La pharmacie a été ajoutée avec succès.',
                        'data' => $pharmacie
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas le profil "propriétaire" pour ajouter une pharmacie.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour ajouter une pharmacie.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'ajout de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function listerPharmacies(Request $request)
    {
        try {
            
            $query = Pharmacie::query();
            $perPage = 12;
            $page = $request->input('page', 1);
            $search = $request->input('search');

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nom', 'LIKE', "%$search%")
                        ->orWhere('adresse', 'LIKE', "%$search%");
                });
            }

            $total = $query->count();
            $resultat = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Les pharmacies ont été récupérées',
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'items' => $resultat
            ]);
        } catch (Exception $e) {
            return response()->json($e);
        }
    }


    public function supprimerPharmacie(Pharmacie $pharmacie)
    {
        try {
            // Vérifiez si l'utilisateur connecté est autorisé à supprimer cette pharmacie
            if (auth()->user()->id === $pharmacie->proprietaire_id) {
                $pharmacie->delete();
                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'La pharmacie a été supprimée avec succès.',
                    'data' => $pharmacie
                ]);
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'êtes pas autorisé à supprimer cette pharmacie.'
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la suppression de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function modifierPharmacie(ModifierPharmacieRequest $request, $id)
    {
        try {
            // Vérifiez d'abord si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Récupérez la pharmacie à modifier
                $pharmacie = Pharmacie::find($id);

                if ($pharmacie) {
                    // Vérifiez si l'utilisateur est le propriétaire de cette pharmacie
                    if ($user->id === $pharmacie->proprietaire_id) {
                        // Mettez à jour les détails de la pharmacie
                        $pharmacie->nom = $request->nom;
                        if ($request->hasFile('photo')) {
                            $imagePath = $request->file('photo');
                            $extension = $imagePath->getClientOriginalExtension();
                            $filename = time() . '.' . $extension;
                            $imagePath->move('images/', $filename);
                            $pharmacie->photo = $filename;
                        }
                        $pharmacie->adresse = $request->adresse;
                        $pharmacie->telephone = $request->telephone;
                        $pharmacie->fax = $request->fax;
                        $pharmacie->latitude = $request->latitude;
                        $pharmacie->longitude = $request->longitude;
                        $pharmacie->quartier_id = $request->quartier_id;
                        $pharmacie->save();

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'La pharmacie a été modifiée avec succès.',
                            'data' => $pharmacie
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'êtes pas autorisé à modifier cette pharmacie.'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 404,
                        'status_message' => 'La pharmacie que vous essayez de modifier est introuvable.'
                    ], 404);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour modifier une pharmacie.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la modification de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function detailsPharmacie($id)
    {
        try {
            $pharmacie = Pharmacie::find($id);
    
            if (!$pharmacie) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Pharmacie non trouvée',
                ], 404);
            }
    
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Détails de la pharmacie récupérés avec succès',
                'pharmacie' => $pharmacie,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur serveur lors de la récupération des détails de la pharmacie',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


     public function listerAgentsPharmacie($id)
    {
        try {
            $users = User::select('users.*')
                ->join('pharmacie_user_agent as pu', 'pu.agentPharmacie_id', '=', 'users.id')
                ->where('pu.pharmacie_id', '=', $id)
                ->get();

            if (!$users) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Agents Pharmacie non trouvée',
                ], 404);
            }
    
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Agents de la pharmacie récupérés avec succès',
                'agents' => $users,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur serveur lors de la récupération des agents de la pharmacie',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function horairesPharmacie($pharmacieId)
    {
        try {
            $horaires = Horaire::where('pharmacie_id', '=', $pharmacieId)->get();

            if (!$horaires) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Agents Pharmacie non trouvée',
                ], 404);
            }
    
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Agents de la pharmacie récupérés avec succès',
                'horaires' => $horaires,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur serveur lors de la récupération des agents de la pharmacie',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

//     public function getPharmacieByProprietaireId($id)
// {
//     try {
//         $pharmacie = Pharmacie::find($id);

//         if (!$pharmacie) {
//             return response()->json([
//                 'status_code' => 404,
//                 'status_message' => 'Pharmacie non trouvée',
//             ], 404);
//         }

//         return response()->json([
//             'status_code' => 200,
//             'status_message' => 'ID de la pharmacie récupéré avec succès',
//             'pharmacie_id' => $pharmacie->id,
//         ]);
//     } catch (Exception $e) {
//         return response()->json([
//             'status_code' => 500,
//             'status_message' => 'Erreur serveur lors de la récupération de l\'ID de la pharmacie',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }
public function getPharmacieByProprietaireId($proprietaireId)
{
    try {
        $proprietaire = Proprietaire::find($proprietaireId);

        if (!$proprietaire) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Propriétaire non trouvé',
            ], 404);
        }

        // Récupérer les pharmacies associées au propriétaire
        $pharmaciesDuProprietaire = $proprietaire->pharmacies;

        // Extraire les IDs des pharmacies
        $idsPharmacies = $pharmaciesDuProprietaire->pluck('id')->toArray();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'IDs des pharmacies du propriétaire récupérés avec succès',
            'ids_pharmacies' => $idsPharmacies,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur serveur lors de la récupération des IDs des pharmacies du propriétaire',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function getPharmacieByProprietaireIds($proprietaireId)
{
    try {
        // Vérifiez si l'utilisateur est connecté
        if (auth()->check() && auth()->user()->profile === 'proprietaire') {
            $proprietaire = Auth::user();

            // Vérifiez si l'utilisateur connecté est le propriétaire de la pharmacie
            if ($proprietaire->id == $proprietaireId) {
                // Récupérez la pharmacie associée au propriétaire
                $pharmacie = $proprietaire->pharmacie;

                if (!$pharmacie) {
                    return response()->json([
                        'status_code' => 404,
                        'status_message' => 'Pharmacie non trouvée pour le propriétaire spécifié.',
                    ], 404);
                }

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Pharmacie récupérée avec succès.',
                    'pharmacie' => $pharmacie,
                ], 200);
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour accéder à cette pharmacie.',
                ], 403);
            }
        } else {
            return response()->json([
                'status_code' => 401,
                'status_message' => 'Vous devez être connecté en tant que propriétaire pour accéder à cette fonctionnalité.',
            ], 401);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération de la pharmacie.',
            'error' => $e->getMessage(),
        ], 500);
    }
}



}
