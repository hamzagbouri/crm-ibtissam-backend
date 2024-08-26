<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'date', 'quantity', 'produit_id', 'matierePremiere_id', 'user_id'];

    public function produit()
    {
        return $this->belongsTo(ProduitFini::class);
    }

    public function matierePremiere()
    {
        return $this->belongsTo(MatierePremiere::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class);
    }
}
