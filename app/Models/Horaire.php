<?php

namespace App\Models;

use App\Models\Pharmacie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Horaire extends Model
{
    use HasFactory;
    protected $fillable = [
        'j1',
        'j2',
        'j3',
        'j4',
        'j5',
        'j6',
        'j7',
        'statut',
        'pharmacie_id'
    ];
    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class,'pharmacie_id');
    }
}
