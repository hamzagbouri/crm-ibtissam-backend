<?php

namespace App\Http\Controllers;

use App\Models\MatierePremiere;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatierePremiereController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $matieres = MatierePremiere::all();
        return response()->json($matieres);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'unit' => 'required|string|max:50',
            'priceU' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $matiere = MatierePremiere::create($request->only(['name', 'quantity', 'unit','priceU']));

        MouvementStock::create([
            'type' => 'entre',
            'date' => now(),
            'quantity' => $matiere->quantity,
            'matierePremiere_id' => $matiere->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json($matiere, 201);
    }

    public function show($id)
    {
        $matiere = MatierePremiere::findOrFail($id);
        return response()->json($matiere);
    }

    public function update(Request $request, $id)
    {
        $matiere = MatierePremiere::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer',
            'unit' => 'sometimes|required|string|max:50',
            'priceU'  => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldQuantity = $matiere->quantity;
        $matiere->update($request->only(['name', 'quantity', 'unit','priceU']));

        $quantityDifference = $matiere->quantity - $oldQuantity;

        if ($quantityDifference != 0) {
            MouvementStock::create([
                'type' => $quantityDifference > 0 ? 'entre' : 'sortie',
                'date' => now(),
                'quantity' => abs($quantityDifference),
                'matierePremiere_id' => $matiere->id,
                'user_id' => auth()->id(),
            ]);
        }

        return response()->json($matiere);
    }

    public function destroy($id)
    {
        $matiere = MatierePremiere::findOrFail($id);
        MouvementStock::create([
            'type' => 'sortie',
            'date' => now(),
            'quantity' => $matiere->quantity,
            'matierePremiere_id' => $matiere->id,
            'user_id' => auth()->id(),
        ]);
        $matiere->delete();

       

        return response()->json(null, 204);
    }
}
