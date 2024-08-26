<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdresseIPAutorisee extends Model
{
    use HasFactory;
    protected $fillable = ['addressIP', 'user_id'];

    public function utilisateur() {
        return $this->belongsTo(User::class);
    }
}
