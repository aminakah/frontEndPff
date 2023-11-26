<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\Horaire;
use App\Models\Produit;
use App\Models\Categorie;
use App\Models\Pharmacie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\AjouterProduitRequest;
use App\Http\Requests\ModifierProduitRequest;
use App\Http\Requests\AjouterCategorieRequest;
use App\Http\Requests\ModifierCategorieRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProprietaireController extends Controller
{


    public function ajouterAgentPharmacie(RegisterUserRequest $request, $pharmacieId)
    {
        try {
            // Vérifiez l'authentification et le profil du propriétaire
            $proprietaire = auth()->user();

            if ($proprietaire->profile !== 'proprietaire') {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour ajouter un agent de pharmacie à votre pharmacie.',
                ], 403);
            }

            // Recherchez la pharmacie associée au propriétaire actuellement connecté
            $pharmacieDuProprietaire = $proprietaire->pharmacie()->find($pharmacieId);

            if (!$pharmacieDuProprietaire) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Pharmacie non trouvée pour ce propriétaire. Impossible d\'ajouter un agent.',
                ], 404);
            }

            // Utilisez une transaction pour garantir la cohérence des données
            DB::beginTransaction();

            try {
                // Créez un nouvel utilisateur avec le profil "agentPharmacie"
                $agentPharmacie = new User();
                $agentPharmacie->fill([
                    'prenom' => $request->prenom,
                    'nom' => $request->nom,
                    'email' => $request->email,
                    'adresse' => $request->adresse,
                    'telephone' => $request->telephone,
                    'profile' => 'agentPharmacie',
                    'password' => $request->password,
                ]);
                $agentPharmacie->save();

                // Associez l'utilisateur "agentPharmacie" à la pharmacie sélectionnée
                $pharmacieDuProprietaire->agentPharmacie()->attach($agentPharmacie->id);

                DB::commit();

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'L\'agent pharmacie a été ajouté et associé à la pharmacie avec succès.',
                    'data' => ['pharmacie' => $pharmacieDuProprietaire, 'agentPharmacie' => $agentPharmacie]
                ], 200);
            } catch (\Exception $e) {
                // En cas d'erreur, annulez la transaction
                DB::rollBack();

                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Erreur lors de l\'ajout de l\'agent pharmacie et de son association à votre pharmacie.',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur générale lors de l\'ajout de l\'agent pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listerMesAgentPharmacie(Request $request)
    {
        try {
            if (auth()->check() && auth()->user()->profile === 'proprietaire') {
                $proprietaire = Auth::user();

                // Récupérez les pharmacies associées au propriétaire
                $pharmacie = $proprietaire->pharmacie;

                // Vous pouvez maintenant récupérer les agents de pharmacie associés à ces pharmacies
                $agentPharmacies = collect();
                $query = User::query();
                $perPage = 12;
                $page = $request->input('page', 1);
                $search = $request->input('search');

                foreach ($pharmacie as $pharmacies) {
                    $agents = $pharmacies->agentPharmacie;

                    if ($search) {
                        $agents = $agents->where(function ($query) use ($search) {
                            $query->where('prenom', 'LIKE', "%$search%")
                                ->orWhere('nom', 'LIKE', "%$search%")
                                ->orWhere('adresse', 'LIKE', "%$search%")
                                ->orWhere('telephone', 'LIKE', "%$search%")
                                ->orWhere('email', 'LIKE', "%$search%");
                        });
                    }

                    $agentPharmacies = $agentPharmacies->concat($agents);
                }

                // Vérifiez s'il n'y a aucun résultat pour la recherche
                if ($agentPharmacies->isEmpty()) {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Aucun agent de pharmacie trouvé avec ce nom.',
                    ], 404);
                }

                $total = $agentPharmacies->count();
                $resultat = $agentPharmacies->skip(($page - 1) * $perPage)->take($perPage);

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Les agents de pharmacie ont été récupérés',
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'items' => $resultat
                ]);
            }
        } catch (Exception $e) {
            return response()->json($e);
        }
    }

    // public function listerMesPharmacies(Request $request)
    // {
    //     try {
    //         // Vérifiez si l'utilisateur est connecté et est un propriétaire de pharmacie
    //         if (auth()->check() && Auth::user()->profile === 'proprietaire') {
    //             $proprietaire = Auth::user();
    //             $pharmacie = $proprietaire->pharmacie();

    //             // Effectuez une recherche si un terme de recherche est fourni
    //             $search = $request->input('search');
    //             if ($search) {
    //                 $pharmacie->where(function ($query) use ($search) {
    //                     $query->where('nom', 'like', '%' . $search . '%')
    //                         ->orWhere('region', 'like', '%' . $search . '%')
    //                         ->orWhere('departement', 'like', '%' . $search . '%')
    //                         ->orWhere('quartier', 'like', '%' . $search . '%')
    //                         ->orWhere('adresse', 'like', '%' . $search . '%');
    //                 });
    //             }

    //             $pharmacies = $pharmacie->get();

    //             return response()->json([
    //                 'status_code' => 200,
    //                 'status_message' => 'Liste de vos pharmacies récupérée avec succès.',
    //                 'data' => $pharmacies
    //             ], 200);
    //         } else {
    //             return response()->json([
    //                 'status_code' => 403,
    //                 'status_message' => 'Vous n\'avez pas les autorisations pour lister vos pharmacies.'
    //             ], 403);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status_code' => 500,
    //             'status_message' => 'Erreur lors de la récupération de la liste de vos pharmacies.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function listerMesPharmacies(Request $request)
    {
        try {
            // Vérifiez si l'utilisateur est connecté et est un propriétaire de pharmacie
            if (auth()->check() && Auth::user()->profile === 'proprietaire') {
                $proprietaire = Auth::user();
                $pharmacie = $proprietaire->pharmacie();
    
                // Effectuez une recherche si un terme de recherche est fourni
                $search = $request->input('search');
                if ($search) {
                    $pharmacie->where(function ($query) use ($search) {
                        $query->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('region', 'like', '%' . $search . '%')
                            ->orWhere('departement', 'like', '%' . $search . '%')
                            ->orWhere('quartier', 'like', '%' . $search . '%')
                            ->orWhere('adresse', 'like', '%' . $search . '%');
                    });
                }
    
                // Include the quartier name in the response
                $pharmacies = $pharmacie->with('quartier:id,nom')->get();
    
                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Liste de vos pharmacies récupérée avec succès.',
                    'data' => $pharmacies
                ], 200);
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour lister vos pharmacies.'
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération de la liste de vos pharmacies.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function listerIdMesPharmacies(Request $request)
    {
        try {
            // Vérifiez si l'utilisateur est connecté et est un propriétaire de pharmacie
            if (auth()->check() && Auth::user()->profile === 'proprietaire') {
                $proprietaire = Auth::user();
                $pharmacie = $proprietaire->pharmacie();
    
                // Effectuez une recherche si un terme de recherche est fourni
                $search = $request->input('search');
                if ($search) {
                    $pharmacie->where(function ($query) use ($search) {
                        $query->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('region', 'like', '%' . $search . '%')
                            ->orWhere('departement', 'like', '%' . $search . '%')
                            ->orWhere('quartier', 'like', '%' . $search . '%')
                            ->orWhere('adresse', 'like', '%' . $search . '%');
                    });
                }
    
                $pharmacies = $pharmacie->get();
    
                // Récupérez uniquement les ID de vos pharmacies
                $pharmacyIds = $pharmacies->pluck('id');
    
                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Liste de vos pharmacies sdfvrécupérée avec succès.',
                    'data' => [
                        // 'pharmacies' => $pharmacies,
                        'pharmacy_ids' => $pharmacyIds
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour lister vos pharmacies.'
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération de la liste de vos pharmacies.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
 

    public function ajouterCategorieParPharmacie(AjouterCategorieRequest $request, $pharmacieId){
        try {
            if (auth()->check() && auth()->user()->profile === 'proprietaire') {
                $proprietaire = auth()->user();
                $pharmacie = $proprietaire->pharmacie->find($pharmacieId);

                if ($pharmacie) {
                    $categorie = new Categorie();
                    $categorie->nom = $request->nom;
                    $categorie->description = $request->description;
                    $categorie->pharmacie_id = $pharmacie->id;
                    $categorie->save();

                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'categorie de medicament enregistrés avec succès dans la pharmacies.',
                        'data' => $categorie
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 404,
                        'status_message' => 'Pharmacie non trouvée pour ce propriétaire. Impossible d\'enregistrer la categorie de medicaments.',
                    ], 404);
                }
            }
            else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour enregistrer les categories de medicaments de cette pharmacie.',
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'enregistrement des categories de medicaments de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function modifierCategorieParPharmacie(ModifierCategorieRequest $request, $pharmacieId, $categorieId)
    {
        try {
            if (auth()->check() && auth()->user()->profile === 'proprietaire') {
                $proprietaire = auth()->user();
                $pharmacie = $proprietaire->pharmacie->find($pharmacieId);

                if ($pharmacie) {
                    $categorie = Categorie::where('pharmacie_id', $pharmacie->id)->find($categorieId);

                    if ($categorie) {
                        $categorie->nom = $request->nom;
                        $categorie->description = $request->description;
                        $categorie->save();

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'Catégorie de médicaments modifiée avec succès dans la pharmacie.',
                            'data' => $categorie
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 404,
                            'status_message' => 'Catégorie de médicaments non trouvée dans cette pharmacie. Impossible de modifier.',
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'status_code' => 404,
                        'status_message' => 'Pharmacie non trouvée pour ce propriétaire. Impossible de modifier la catégorie de médicaments.',
                    ], 404);
                }
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour modifier les catégories de médicaments de cette pharmacie.',
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la modification de la catégorie de médicaments de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function supprimerCategorieParPharmacie($pharmacieId, $categorieId)
    {
        try {
            $proprietaire = auth()->user();

            if ($proprietaire->profile !== 'proprietaire') {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour supprimer les catégories de médicaments de cette pharmacie.',
                ], 403);
            }

            $pharmacie = $proprietaire->pharmacie()->findOrFail($pharmacieId);

            $categorie = Categorie::where('pharmacie_id', $pharmacie->id)->findOrFail($categorieId);

            $categorie->delete();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Catégorie de médicaments supprimée avec succès de la pharmacie.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Ressource non trouvée.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la suppression de la catégorie de médicaments de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listerCategoriesParPharmacie($pharmacieId)
    {
        try {
            // Vous pouvez ajuster la logique selon vos besoins d'authentification
            $proprietaire = auth()->user();

            if ($proprietaire->profile !== 'proprietaire') {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour lister les catégories de médicaments.',
                ], 403);
            }

            // Vérifiez si la pharmacie spécifiée appartient au propriétaire
            $pharmacie = $proprietaire->pharmacie()->find($pharmacieId);

            if (!$pharmacie) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Pharmacie non trouvée pour ce propriétaire.',
                ], 404);
            }

            // Récupérez toutes les catégories liées à la pharmacie spécifiée
            $categories = Categorie::where('pharmacie_id', $pharmacie->id)->get();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Liste des catégories de médicaments récupérée avec succès pour la pharmacie spécifiée.',
                'categories' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération de la liste des catégories de médicaments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    public function bloquerDebloquerAgentPharmacie($agentPharmacieId)
    {
        try {
            // Vérifiez si l'utilisateur est authentifié et a le profil "proprietaire"
            $proprietaire = auth()->user();

            if (!$proprietaire || $proprietaire->profile !== 'proprietaire') {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour bloquer/débloquer un agent de pharmacie.',
                ], 403);
            }

            // Recherchez l'agent de pharmacie spécifié
            $agentPharmacie = User::find($agentPharmacieId);

            if (!$agentPharmacie || $agentPharmacie->profile !== 'agentPharmacie') {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Agent de pharmacie non trouvé.',
                ], 404);
            }

            // Vérifiez si l'agent de pharmacie appartient à une pharmacie associée au propriétaire
            //$pharmacieDuProprietaire = $agentPharmacie->pharmacie->where('proprietaire_id', $proprietaire->id)->first();
            $pharmaciesDuProprietaire = $proprietaire->pharmacie->pluck('id')->toArray();
            $pharmacieDeLAgent = $agentPharmacie->agentPharmacie()->first();
            if (!$pharmacieDeLAgent || !in_array($pharmacieDeLAgent->id, $pharmaciesDuProprietaire)) {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous ne pouvez pas bloquer/débloquer un agent de pharmacie qui n\'appartient pas à votre pharmacie.',
                ], 403);
            }

            // Basculez l'état "status"
            $agentPharmacie->update([
                'status' => !$agentPharmacie->status,
            ]);

            $statusMessage = $agentPharmacie->status ? 'Agent de pharmacie bloqué.' : 'Agent de pharmacie débloqué.';

            return response()->json([
                'status_code' => 200,
                'status_message' => $statusMessage,
                'agentPharmacie' => $agentPharmacie,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors du blocage/déblocage de l\'agent de pharmacie.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function ajouterProduitParPharmacie(AjouterProduitRequest $request, $pharmacieId)
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un propriétaire
                if ($user->profile === 'proprietaire') {
                    // Vérifiez si la pharmacie spécifiée appartient au propriétaire
                    $pharmacie = $user->pharmacie()->find($pharmacieId);

                    if ($pharmacie) {
                        // Vérifiez si la catégorie spécifiée existe dans la pharmacie associée au propriétaire
                        $categorieExiste = Categorie::where('id', $request->categorie_id)
                            ->where('pharmacie_id', $pharmacie->id)
                            ->exists();

                        if (!$categorieExiste) {
                            return response()->json([
                                'status_code' => 400,
                                'status_message' => 'La catégorie spécifiée n\'existe pas dans la pharmacie associée au propriétaire.'
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
                        $produit->pharmacie_id = $pharmacie->id;
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
                            'status_message' => 'La pharmacie spécifiée ne vous appartient pas. Choisissez une pharmacie valide.'
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
    public function modifierProduitParPharmacie(ModifierProduitRequest $request, $pharmacieId, $produitId)
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un propriétaire
                if ($user->profile === 'proprietaire') {
                    // Vérifiez si la pharmacie spécifiée appartient au propriétaire
                    $pharmacie = $user->pharmacie()->find($pharmacieId);

                    if ($pharmacie) {
                        // Récupérez le produit à modifier
                        $produit = Produit::find($produitId);

                        if ($produit) {
                            // Vérifiez si le produit appartient à la pharmacie du propriétaire
                            if ($produit->pharmacie_id === $pharmacie->id) {
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
                                $produit->categorie_id = $request->categorie_id;

                                $produit->update();

                                return response()->json([
                                    'status_code' => 200,
                                    'status_message' => 'Produit modifié avec succès.',
                                    'data' => $produit
                                ], 200);
                            } else {
                                return response()->json([
                                    'status_code' => 403,
                                    'status_message' => 'Vous n\'avez pas les autorisations pour modifier ce produit dans cette pharmacie.'
                                ], 403);
                            }
                        } else {
                            return response()->json([
                                'status_code' => 404,
                                'status_message' => 'Le produit spécifié est introuvable.'
                            ], 404);
                        }
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'La pharmacie spécifiée ne vous appartient pas. Choisissez une pharmacie valide.'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour modifier un produit.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour modifier un produit.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la modification du produit.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function supprimerProduitParPharmacie($pharmacieId, $produitId)
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un propriétaire
                if ($user->profile === 'proprietaire') {
                    // Vérifiez si la pharmacie spécifiée appartient au propriétaire
                    $pharmacie = $user->pharmacie()->find($pharmacieId);

                    if ($pharmacie) {
                        // Récupérez le produit à supprimer
                        $produit = Produit::find($produitId);

                        if ($produit) {
                            // Vérifiez si le produit appartient à la pharmacie du propriétaire
                            if ($produit->pharmacie_id === $pharmacie->id) {
                                // Supprimez le produit
                                $produit->delete();

                                return response()->json([
                                    'status_code' => 200,
                                    'status_message' => 'Produit supprimé avec succès.'
                                ], 200);
                            } else {
                                return response()->json([
                                    'status_code' => 403,
                                    'status_message' => 'Vous n\'avez pas les autorisations pour supprimer ce produit dans cette pharmacie.'
                                ], 403);
                            }
                        } else {
                            return response()->json([
                                'status_code' => 404,
                                'status_message' => 'Le produit spécifié est introuvable.'
                            ], 404);
                        }
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'La pharmacie spécifiée ne vous appartient pas. Choisissez une pharmacie valide.'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour supprimer un produit.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour supprimer un produit.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la suppression du produit.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function listerProduitsParPharmacie($pharmacieId)
    {
        try {
            // Vérifiez si l'utilisateur est connecté
            if (auth()->check()) {
                $user = auth()->user();

                // Vérifiez si l'utilisateur est un propriétaire
                if ($user->profile === 'proprietaire') {
                    // Vérifiez si la pharmacie spécifiée appartient au propriétaire
                    $pharmacie = $user->pharmacie()->find($pharmacieId);

                    if ($pharmacie) {
                        // Récupérez les produits de la pharmacie spécifiée
                        $produits = Produit::where('pharmacie_id', $pharmacie->id)->get();

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'Liste des produits récupérée avec succès.',
                            'produits' => $produits,
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 403,
                            'status_message' => 'La pharmacie spécifiée ne vous appartient pas. Choisissez une pharmacie valide.'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour lister les produits.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'Vous devez être connecté pour lister les produits.'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération de la liste des produits.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function enregistrerHorairesPharmacie(Request $request, $pharmacieId)
    {
        try {
            if (auth()->check() && auth()->user()->profile === 'proprietaire') {
                $proprietaire = auth()->user();
                $pharmacie = $proprietaire->pharmacie->find($pharmacieId);

                if ($pharmacie) {
                    $horaires = new Horaire();
                    $horaires->j1 = $request->input('j1');
                    $horaires->j2 = $request->input('j2');
                    $horaires->j3 = $request->input('j3');
                    $horaires->j4 = $request->input('j4');
                    $horaires->j5 = $request->input('j5');
                    $horaires->j6 = $request->input('j6');
                    $horaires->j7 = $request->input('j7');
                    //$horaires->status = $request->input('status');
                    $horaires->pharmacie_id = $pharmacie->id;
                    $horaires->save();

                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'Horaires de la pharmacie enregistrés avec succès.',
                        'data' => $horaires
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 404,
                        'status_message' => 'Pharmacie non trouvée pour ce propriétaire. Impossible d\'enregistrer les horaires.',
                    ], 404);
                }
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour enregistrer les horaires de cette pharmacie.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'enregistrement des horaires de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function modifierHorairesPharmacie(Request $request, $pharmacieId)
    {
        try {
            if (auth()->check() && auth()->user()->profile === 'proprietaire') {
                $proprietaire = auth()->user();
                $pharmacie = $proprietaire->pharmacie->find($pharmacieId);

                if ($pharmacie) {
                    $horaires = Horaire::where('pharmacie_id', $pharmacie->id)->first();
                    if ($horaires) {
                        // Mettez à jour les horaires avec les nouvelles données
                        $horaires->update([
                            'j1' => $request->input('j1'),
                            'j2' => $request->input('j2'),
                            'j3' => $request->input('j3'),
                            'j4' => $request->input('j4'),
                            'j5' => $request->input('j5'),
                            'j6' => $request->input('j6'),
                            'j7' => $request->input('j7'),
                           // 'status' => $request->input('status'),
                        ]);

                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'Horaires de la pharmacie modifiés avec succès.',
                            'data' => $horaires
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 404,
                            'status_message' => 'Horaires introuvables pour cette pharmacie. Impossible de les modifier.',
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'status_code' => 403,
                        'status_message' => 'Vous n\'avez pas les autorisations pour modifier les horaires de cette pharmacie.',
                    ], 403);
                }
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour modifier les horaires de cette pharmacie.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la modification des horaires de la pharmacie.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listerHorairesPharmacie($pharmacieId)
    {
        try {
            // Vérifiez si l'utilisateur est authentifié et a le profil "proprietaire"
            if (auth()->check() && auth()->user()->profile === 'proprietaire') {
                $proprietaire = auth()->user();

                // Utilisez la relation directe pour obtenir la pharmacie
                $pharmacie = $proprietaire->pharmacie()->find($pharmacieId);

                if ($pharmacie) {
                    // Récupérez les horaires de la pharmacie
                    $horaires = $pharmacie->horaire;

                    return response()->json([
                        'status_code' => 200,
                        'status_message' => 'Liste des horaires récupérée avec succès.',
                        'horaires' => $horaires,
                    ], 200);
                } else {
                    return response()->json([
                        'status_code' => 404,
                        'status_message' => 'Pharmacie non trouvée pour ce propriétaire. Impossible de récupérer les horaires.',
                    ], 404);
                }
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour lister les horaires de cette pharmacie.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération des horaires de la pharmacie.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function definirStatutGardePharmacie($pharmacieId)
{
    try {
        // Vérifiez si l'utilisateur est authentifié et a le profil "proprietaire"
        if (auth()->check() && auth()->user()->profile === 'proprietaire') {
            $proprietaire = auth()->user();

            // Recherchez la pharmacie associée au propriétaire
            $pharmacie = $proprietaire->pharmacie()->find($pharmacieId);

            if ($pharmacie) {
                // Récupérez le statut de garde de la pharmacie
                $status = $pharmacie->horaire->first()->status ?? null;
                $nouveauStatut = ($status === 0) ? 1 : 0;
                // Mettez à jour le statut de garde de la pharmacie
                $pharmacie->horaire()->update(['status' => $nouveauStatut]);

                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Statut de garde de la pharmacie mis à jour avec succès.',
                    'nouveau_statut' => $nouveauStatut,
                ], 200);
            } else {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Pharmacie non trouvée pour ce propriétaire. Impossible de mettre à jour le statut de garde.',
                ], 404);
            }
        } else {
            return response()->json([
                'status_code' => 403,
                'status_message' => 'Vous n\'avez pas les autorisations pour mettre à jour le statut de garde de cette pharmacie.',
            ], 403);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la mise à jour du statut de garde de la pharmacie.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function obtenirStatutGardePharmacie($pharmacieId)
    {
        try {
            // Vérifiez si l'utilisateur est authentifié et a le profil "proprietaire"
            if (auth()->check() && auth()->user()->profile === 'proprietaire') {
                $proprietaire = auth()->user();

                // Recherchez la pharmacie associée au propriétaire
                $pharmacie = $proprietaire->pharmacie()->find($pharmacieId);

                if ($pharmacie) {
                    // Récupérez le statut de garde de la pharmacie
                    $status = $pharmacie->horaire->first()->status ?? null;

                    if ($status === 0) {
                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'La pharmacie n\'est pas en garde aujourd\'hui.',
                        ], 200);
                    } elseif ($status === 1) {
                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'La pharmacie est en garde aujourd\'hui.',
                        ], 200);
                    } else {
                        return response()->json([
                            'status_code' => 200,
                            'status_message' => 'Le statut de garde de la pharmacie est indéterminé.',
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status_code' => 404,
                        'status_message' => 'Pharmacie non trouvée pour ce propriétaire.',
                    ], 404);
                }
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour obtenir le statut de garde de cette pharmacie.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération du statut de garde de la pharmacie.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function listerHorairesPharmacies()
    {
        try {
            // Vérifiez si l'utilisateur est authentifié et a le profil "proprietaire"
            if (auth()->check() && auth()->user()->profile === 'proprietaire') {
                $proprietaire = auth()->user();
    
                // Utilisez la relation directe pour obtenir toutes les pharmacies du propriétaire
                $pharmacies = $proprietaire->pharmacie;
    
                $horaires = [];
    
                foreach ($pharmacies as $pharmacie) {
                    // Récupérez les identifiants des horaires de chaque pharmacie
                    $horairesPharmacieIds = $pharmacie->horaire->pluck('id')->toArray();
    
                    // Ajoutez les identifiants des horaires de cette pharmacie à la liste globale
                    $horaires = array_merge($horaires, $horairesPharmacieIds);
                }
    
                // Récupérez les horaires complets en fonction des identifiants
                $horairesComplets = Horaire::whereIn('id', $horaires)->get();
    
                return response()->json([
                    'status_code' => 200,
                    'status_message' => 'Liste des horaires de toutes les pharmacies récupérée avec succès.',
                    'horaires' => $horairesComplets,
                ], 200);
            } else {
                return response()->json([
                    'status_code' => 403,
                    'status_message' => 'Vous n\'avez pas les autorisations pour lister les horaires de toutes les pharmacies.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération des horaires de toutes les pharmacies.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}    
