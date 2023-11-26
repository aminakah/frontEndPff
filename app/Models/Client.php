<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    protected $fillable = ['client_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
