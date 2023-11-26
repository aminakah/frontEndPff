<?php

namespace App\Models;

use App\Models\Departement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory;
    protected $fillable = ['nom'];
    public function departements()
    {
        return $this->hasMany(Departement::class);
    }
}
