<?php

namespace App\Http\Controllers\Api;

use App\Models\Produit;
use App\Models\Categorie;
use Illuminate\Http\Request;
use App\Models\PharmacieAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\AjouterProduitRequest;
use App\Http\Requests\ModifierProduitRequest;

class ProduitController extends Controller
{
    public function ajouterProduit(AjouterProduitRequest $request)
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un agent de pharmacie associé
                if ($user->profile === 'agentPharmacie') {
                    $userPharmacieAgent = $user->pharmacie_agent;
                    // Vérifiez si l'utilisateur est associé à une pharmacie en tant qu'agent de pharmacie
                    if ($userPharmacieAgent) {
                        $pharmacieAssociee = $userPharmacieAgent->pharmacie;
                        // Vérifiez si la catégorie spécifiée existe dans la pharmacie associée à l'agent de pharmacie
                        $categorieExiste = Categorie::where('id', $request->categorie_id)
                            ->where('pharmacie_id', $pharmacieAssociee->id)
                            ->exists();

                        if (!$categorieExiste) {
                            return response()->json([
                                'status_code' => 400,
                                'status_message' => 'La catégorie spécifiée n\'existe pas dans la pharmacie associée à l\'agent de pharmacie.'
                            ], 400);
                        }

                        // Créer un nouveau produit
                        $produit = new Produit();
                        $produit->nom = $request->nom;
                        if ($request->hasFile('photo')) {
                            $imagePath = $request->file('photo');
                            $extension = $imagePath->getClientOriginalExtension();
                            $filename = time() . '.' . $extension;
                            $imagePath->move('images/', $filename);
                            $produit->photo = $filename;
                        }
                        $produit->description = $request->description;
                        $produit->prix = $request->prix;
                        $produit->quantite = $request->quantite;
                        $produit->date_expiration = $request->date_expiration;
                        $produit->pharmacie_id = $user->pharmacie_agent->pharmacie_id;
                        $produit->categorie_id = $request->categorie_id;

                        $produit->save();

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'Produit ajouté avec succès.',
                            'data' => $produit
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous devez être associé à une pharmacie en tant qu\'agent de pharmacie. Choisissez une pharmacie.'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour ajouter un produit.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour ajouter un produit.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'ajout du produit.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function supprimerProduit($produitId)
{
    try {
        // Vérifiez si l'utilisateur est connecté
        if (auth()->check()) {
            $user = auth()->user();

            // Récupérez le produit à supprimer
            $produit = Produit::find($produitId);

            if ($produit) {
                // Vérifiez si l'utilisateur a le droit de supprimer le produit
                $userPharmacieAgent = $user->pharmacie_agent;
                if ($user->profile === 'agentPharmacie' && $userPharmacieAgent) {
                    // Vérifiez si le produit appartient à la pharmacie associée à l'agent
                    if ($produit->pharmacie_id === $userPharmacieAgent->pharmacie_id) {
                        // Supprimez le produit
                        $produit->delete();

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'Produit supprimé avec succès.',
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'avez pas les autorisations pour supprimer ce produit.',
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour supprimer ce produit.',
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Le produit spécifié est introuvable.',
                ], 404);
            }
        } else {
            return response()->json([
                'status_code' => 401,
                'status_message' => 'Vous devez être connecté pour supprimer un produit.',
            ], 401);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la suppression du produit.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function modifierProduit(ModifierProduitRequest $request, $produitId)
{
    try {
        // Vérifiez si l'utilisateur est connecté
        if (auth()->check()) {
            $user = auth()->user();

            // Récupérez le produit à modifier
            $produit = Produit::find($produitId);

            if ($produit) {
                // Vérifiez si l'utilisateur a le droit de modifier le produit
                $userPharmacieAgent = $user->pharmacie_agent;
                if ($user->profile === 'agentPharmacie' && $userPharmacieAgent) {
                    // Vérifiez si le produit appartient à la pharmacie associée à l'agent
                    if ($produit->pharmacie_id === $userPharmacieAgent->pharmacie_id) {
                        // Mettez à jour les informations du produit
                        $produit->nom = $request->nom;
                        if ($request->hasFile('photo')) {
                            $imagePath = $request->file('photo');
                            $extension = $imagePath->getClientOriginalExtension();
                            $filename = time() . '.' . $extension;
                            $imagePath->move('images/', $filename);
                            $produit->photo = $filename;
                        }
                        $produit->description = $request->description;
                        $produit->prix = $request->prix;
                        $produit->quantite = $request->quantite;
                        $produit->date_expiration = $request->date_expiration;
                        $produit->categorie_id = $request->categorie_id; // Assurez-vous que la catégorie spécifiée existe dans la pharmacie

                        $produit->save();

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'Produit modifié avec succès.',
                            'data' => $produit,
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'avez pas les autorisations pour modifier ce produit.',
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour modifier ce produit.',
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Le produit spécifié est introuvable.',
                ], 404);
            }
        } else {
            return response()->json([
                'status_code' => 401,
                'status_message' => 'Vous devez être connecté pour modifier un produit.',
            ], 401);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la modification du produit.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function listerProduits()
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un agent de pharmacie associé
                // if ($user->profile === 'agentPharmacie' && $user->pharmacie_agent) {

                //     // Récupérez les produits de la pharmacie associée à l'agent de pharmacie
                //     $produits = Produit::where('pharmacie_id', $user->pharmacie_agent->id)->get();

                //     return response()->json([
                //         'status_code' => 200,
                //         'status_message' => 'Liste des produits récupérée avec succès.',
                //         'produits' => $produits,
                //     ], 200);
                // } else {
                //     return response()->json([
                //         'status_code' => 403,
                //         'status_message' => 'Vous n\'avez pas les autorisations pour lister les produits.',
                //     ], 403);
                // }
                if ($user->profile === 'agentPharmacie') {
                    // Récupérez les produits de la pharmacie associée à l'agent de pharmacie
                    $pharmacieAgent = PharmacieAgent::where('agentPharmacie_id', $user->id)->first();

                    if ($pharmacieAgent) {
                        $produits = Produit::where('pharmacie_id', $pharmacieAgent->pharmacie_id)->get();

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'Liste des produits récupérée avec succès.',
                            'produits' => $produits,
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'Vous n\'avez pas les autorisations pour lister les produits.',
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour lister les produits.',
                    ], 403);
                }

            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour lister les produits.',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération de la liste des produits.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function publicListerProduits($pharmacieId){
        try{
            $produits = Produit::where('pharmacie_id', '=',$pharmacieId)->get();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Liste des produits récupérée avec succès.',
                'produits' => $produits,
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération de la liste des produits.',
                'error' => $e->getMessage(),
            ], 500);
    }


}

}
