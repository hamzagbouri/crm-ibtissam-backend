<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitFini extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'quantity', 'unit', "priceU", 'matiere_premiere_quantities'];

    protected $casts = [
        'matiere_premiere_quantities' => 'array', // Cast JSON field to array
    ];

    // No need for pivot table methods; JSON field handles the relation
    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class);
    }
}
