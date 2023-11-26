<?php

namespace App\Models;

use App\Models\Categorie;
use App\Models\Pharmacie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produit extends Model
{
    use HasFactory;
    protected $fillable = ['nom','photo','description', 'prix','quantite','date_expiration','pharmacie_id','categorie_id'];
    public function categorie()
    {
        return $this->belongsTo(Categorie::class,'categorie_id');
    }
    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class,'pharmacie_id');
    }
}
