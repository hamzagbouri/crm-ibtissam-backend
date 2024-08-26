<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MouvementStock;

class movementController extends Controller
{
    public function index()
    {
        $matieres = MouvementStock::all();
        return response()->json($matieres);
    }
}
