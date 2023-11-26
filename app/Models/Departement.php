<?php

namespace App\Models;

use App\Models\Region;
use App\Models\Quartier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departement extends Model
{
    use HasFactory;
    protected $fillable = ['nom','region_id'];
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function quartiers()
    {
        return $this->hasMany(Quartier::class);
    }
}
