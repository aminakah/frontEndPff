<?php

namespace App\Models;

use App\Models\Produit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categorie extends Model
{
    use HasFactory;
    protected $fillable = [ 'nom', 'description','pharmacie_id'];

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class,'pharmacie_id');
    }
    public function produit()
    {
        return $this->hasMany(Produit::class,'categorie_id');
    }
}
