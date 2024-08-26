<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatierePremiere extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'quantity', 'unit',"priceU"];

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function produitsFinis()
    {
        return ProduitFini::whereJsonContains('matiere_premiere_quantities', $this->id)->get();
    }
}
