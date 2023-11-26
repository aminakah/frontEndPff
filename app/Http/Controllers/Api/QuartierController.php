<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Quartier;
use App\Models\Region;
use Exception;
use Illuminate\Http\Request;

class QuartierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function listerQuartiers($idDepartement)
    {
        try {
            // Récupérez la liste des regions
            $quarties = Quartier::where("departement_id", "=",$idDepartement)->get();
            return response()->json($quarties, 200);
            
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération des quartiers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
