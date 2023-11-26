<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    use HasFactory;
    protected $fillable = ['nom','departement_id'];
    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function pharmacies()
    {
        return $this->hasMany(Pharmacie::class,'pharmacie_id');
    }
}
