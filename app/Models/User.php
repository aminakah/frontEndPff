<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Client;
use App\Models\Pharmacie;
use App\Models\Proprietaire;
use App\Models\PharmacieAgent;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prenom',
        'nom',
        'email',
        'adresse',
        'telephone',
        'profile',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function pharmacie()
    {
        return $this->hasMany(Pharmacie::class, 'proprietaire_id');
    }
    public function pharmacie_agent()
    {
        return $this->hasOne(PharmacieAgent::class, 'agentPharmacie_id');
    }

    public function client()
    {
        return $this->hasOne(Client::class,'client_id');
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class,'admin_id');
    }

    public function proprietaire()
    {
        return $this->hasOne(Proprietaire::class,'proprietaire_id');
    }
    public function agentPharmacie()
    {
        return $this->belongsToMany(Pharmacie::class, 'pharmacie_user_agent', 'agentPharmacie_id', 'pharmacie_id');
    }
}
