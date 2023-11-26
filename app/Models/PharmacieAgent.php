<?php

namespace App\Models;

use App\Models\Pharmacie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PharmacieAgent extends Model
{
    use HasFactory;
    protected $table = 'pharmacie_user_agent'; // Nom de la table pivot

    protected $fillable = [
        'pharmacie_id',
        'agentPharmacie_id',
    ];

    // Relation vers le modèle Pharmacie
    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class, 'pharmacie_id');
    }
}
