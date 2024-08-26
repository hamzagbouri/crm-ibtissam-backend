<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;
    protected $fillable = ['client_id','commandeID', "prixTotal",'dateCommande', 'dateLivraison', 'status', 'produits'];

    protected $casts = [
        'produits' => 'array', // Cast the produits field to an array
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
