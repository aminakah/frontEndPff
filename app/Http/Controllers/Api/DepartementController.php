<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Departement;
use Exception;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function listerDepartements($idRegion)
    {
        try {
            // Récupérez la liste des départements
            $departement = Departement::where("region_id", "=", $idRegion)->get();
            return response()->json($departement, 200);
            
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération des départements.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
