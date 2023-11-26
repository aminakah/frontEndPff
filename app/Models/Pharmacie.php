<?php

namespace App\Models;

use App\Models\User;
use App\Models\Horaire;
use App\Models\Quartier;
use App\Models\Categorie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pharmacie extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'photo',
        'adresse',
        'telephone',
        'fax',
        'latitude',
        'longitude',
        'proprietaire_id',
        'quartier_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function proprietaire()
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    // public function agentPharmacie()
    // {
    //     return $this->belongsTo(User::class, 'agentPharmacie_id');
    // }
    // Pharmacie.php
    public function agentPharmacie()
    {
        return $this->belongsToMany(User::class, 'pharmacie_user_agent', 'pharmacie_id', 'agentPharmacie_id');
    }

    public function horaire()
    {
        return $this->hasOne(Horaire::class,'pharmacie_id');
    }
    public function categorie()
    {
        return $this->hasMany(Categorie::class,'pharmacie_id');
    }
    public function quartier()
    {
        return $this->belongsTo(Quartier::class);
    }
    public function produit()
    {
        return $this->hasMany(Produit::class,'pharmacie_id');
    }
}
