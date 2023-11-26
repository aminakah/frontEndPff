<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Categorie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AjouterCategorieRequest;
use App\Http\Requests\ModifierCategorieRequest;

class CategorieController extends Controller
{
    //
    public function ajouterCategorie(AjouterCategorieRequest $request)
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un propriétaire de pharmacie ou un agent de pharmacie associé
                if ( $user->profile === 'agentPharmacie') {

                    // Vérifiez si l'utilisateur est associé à une pharmacie en tant qu'agent de pharmacie
                    if ($user->profile === 'agentPharmacie' && !$user->pharmacie_agent) {
                        // Si l'agent de pharmacie n'est pas associé à une pharmacie, vous pouvez afficher un message d'erreur ou demander à l'utilisateur de choisir une pharmacie.
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'êtes pas encore associé à une pharmacie en tant qu\'agent de pharmacie. Veuillez choisir une pharmacie d\'abord.'
                        ], 403);
                    }

                    // Maintenant, vous pouvez créer la catégorie de médicaments associée à la pharmacie de l'utilisateur
                    $categorie = new Categorie();
                    $categorie->nom = $request->nom;
                    $categorie->description = $request->description;

                    // Associez la catégorie de médicaments à la pharmacie appropriée en fonction du profil de l'utilisateur
                    if ($user->profile === 'agentPharmacie') {
                        $categorie->pharmacie_id = $user->pharmacie_agent->pharmacie_id;
                    }

                    $categorie->save();

                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'Catégorie de médicaments ajoutée avec succès.',
                        'data' => $categorie
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour ajouter une catégorie de médicaments.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour ajouter une catégorie de médicaments.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'ajout de la catégorie de médicaments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function modifierCategorie(ModifierCategorieRequest $request, $id)
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();
                $proprietaires = $user->profile === 'agentPharmacie';
                // Vérifiez si l'utilisateur est un propriétaire de pharmacie ou un agent de pharmacie associé
                if ( $proprietaires) {

                    // Vérifiez si l'utilisateur est associé à une pharmacie en tant qu'agent de pharmacie
                    if ($proprietaires && !$user->pharmacie_agent) {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'êtes pas encore associé à une pharmacie en tant qu\'agent de pharmacie. Veuillez choisir une pharmacie d\'abord.'
                        ], 403);
                    }

                    // Vérifiez si la catégorie de médicaments existe
                    $categorie = Categorie::findOrFail($id);

                    // Vérifiez que la catégorie appartient à la pharmacie de l'utilisateur (propriétaire ou agent)
                   if ($proprietaires && $categorie->pharmacie_id !== $user->pharmacie_agent->pharmacie_id) {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'avez pas les autorisations pour modifier cette catégorie de médicaments.'
                        ], 403);
                    }

                    // Mettez à jour les champs de la catégorie de médicaments
                    $categorie->update([
                        'nom' => $request->nom,
                        'description' => $request->description,
                        // Ajoutez d'autres champs à mettre à jour ici
                    ]);

                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'Catégorie de médicaments modifiée avec succès.',
                        'data' => $categorie
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour modifier une catégorie de médicaments.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour modifier une catégorie de médicaments.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la modification de la catégorie de médicaments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function supprimerCategorie($id)
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un propriétaire de pharmacie ou un agent de pharmacie associé
                if ( $user->profile === 'agentPharmacie') {
                    // Vérifiez si l'utilisateur est associé à une pharmacie en tant qu'agent de pharmacie
                    if ($user->profile === 'agentPharmacie' && !$user->pharmacie_agent) {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'êtes pas encore associé à une pharmacie en tant qu\'agent de pharmacie. Veuillez choisir une pharmacie d\'abord.'
                        ], 403);
                    }

                    // Vérifiez si la catégorie de médicaments existe
                    $categorie = Categorie::findOrFail($id);

                    // Vérifiez que la catégorie appartient à la pharmacie de l'utilisateur (propriétaire ou agent)
                    if ($user->profile === 'agentPharmacie' && $categorie->pharmacie_id !== $user->pharmacie_agent->pharmacie_id) {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'avez pas les autorisations pour supprimer cette catégorie de médicaments.'
                        ], 403);
                    }

                    // Supprimez la catégorie de médicaments
                    $categorie->delete();

                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'Catégorie de médicaments supprimée avec succès.'
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour supprimer une catégorie de médicaments.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour supprimer une catégorie de médicaments.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la suppression de la catégorie de médicaments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listerCategories()
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un agent de pharmacie associé
                if ($user->profile === 'agentPharmacie' && $user->pharmacie_agent) {

                    // Récupérez les catégories de médicaments de la pharmacie associée à l'agent de pharmacie
                    $categories = Categorie::where('pharmacie_id', $user->pharmacie_agent->pharmacie_id)->get();

                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'Liste des catégories récupérée avec succès.',
                        'categories' => $categories,
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour lister les catégories de médicaments.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour lister les catégories de médicaments.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération des catégories de médicaments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
