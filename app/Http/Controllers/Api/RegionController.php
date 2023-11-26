<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Region;
use Exception;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function listerRegions()
    {
        try {
            // Récupérez la liste des régions
            $regions = Region::all();
            return response()->json($regions, 200);
            
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la récupération des régions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
