<?php

namespace App\Http\Controllers;

use App\Models\ProduitFini;
use App\Models\MatierePremiere;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class produitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $produits = ProduitFini::all();
        return response()->json($produits);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'unit' => 'required|string|max:50',
            'priceU' => 'required|string',
            'matiere_premiere_quantities' => 'required|array',
            'matiere_premiere_quantities.*.id' => 'required|exists:matiere_premieres,id',
            'matiere_premiere_quantities.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 427);
        }

        $matierePremieres = collect($request->matiere_premiere_quantities);

        foreach ($matierePremieres as $mp) {
            $matiere = MatierePremiere::find($mp['id']);
            if ($matiere->quantity < $mp['quantity']) {
                return response()->json(['error' => 'Insufficient quantity for ' . $matiere->name], 421);
            }
        }

        foreach ($matierePremieres as $mp) {
            $matiere = MatierePremiere::find($mp['id']);
            $matiere->quantity -= $mp['quantity'];
            $matiere->save();

            MouvementStock::create([
                'type' => 'sortie',
                'date' => now(),
                'quantity' => $mp['quantity'],
                'matierePremiere_id' => $matiere->id,
                'user_id' => auth()->id(),
            ]);
        }

        $produit = ProduitFini::create([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'priceU' => $request->priceU,
            'matiere_premiere_quantities' => $matierePremieres->pluck('quantity', 'id')->all(),
        ]);

        MouvementStock::create([
            'type' => 'entre',
            'date' => now(),
            'quantity' => $produit->quantity,
            'produit_id' => $produit->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json($produit, 201);
    }

    public function show($id)
    {
        $produit = ProduitFini::findOrFail($id);
        return response()->json($produit);
    }

    public function update(Request $request, $id)
    {
        $produit = ProduitFini::findOrFail($id);
    
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer',
            'unit' => 'sometimes|required|string|max:50',
            'priceU' => 'sometimes|string',
            'matiere_premiere_quantities' => 'sometimes|array',
            'matiere_premiere_quantities.*.id' => 'required|exists:matiere_premieres,id',
            'matiere_premiere_quantities.*.quantity' => 'required|integer|min:1',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $produit->update($request->only(['name', 'quantity', 'unit','priceU']));
    
        if ($request->has('matiere_premiere_quantities')) {
            $newQuantities = collect($request->matiere_premiere_quantities)->pluck('quantity', 'id');
            $currentQuantities = collect($produit->matiere_premiere_quantities);
    
            foreach ($newQuantities as $id => $newQuantity) {
                $currentQuantity = $currentQuantities->get($id, 0);
                $difference = $newQuantity - $currentQuantity;
    
                $matiere = MatierePremiere::findOrFail($id);
                if ($matiere->quantity < $difference) {
                    return response()->json(['error' => 'Insufficient quantity for ' . $matiere->name], 410);
                }
    
                $matiere->quantity -= $difference;
                $matiere->save();
    
                MouvementStock::create([
                    'type' => $difference > 0 ? 'sortie' : 'retour',
                    'date' => now(),
                    'quantity' => abs($difference),
                    'matierePremiere_id' => $matiere->id,
                    'user_id' => auth()->id(),
                ]);
            }
    
            $produit->update([
                'matiere_premiere_quantities' => $newQuantities->toArray(),
            ]);
        }
    
        return response()->json($produit);
    }
    public function destroy($id)
    {
        $produit = ProduitFini::findOrFail($id);
        $matierePremiereQuantities = $produit->matiere_premiere_quantities;
    
        // Log the deletion of the product
        $logEntry = MouvementStock::create([
            'type' => 'sortie',
            'date' => now(),
            'quantity' => $produit->quantity,
            'produit_id' => $produit->id,
            'user_id' => auth()->id(),
        ]);
    
        // Restore quantities of matiere_premieres
        foreach ($matierePremiereQuantities as $matierePremiereId => $quantity) {
            $matiere = MatierePremiere::find($matierePremiereId);
            if ($matiere) {
                $matiere->quantity += $quantity;
                $matiere->save();
    
                MouvementStock::create([
                    'type' => 'retour',
                    'date' => now(),
                    'quantity' => $quantity,
                    'matierePremiere_id' => $matierePremiereId,
                    'user_id' => auth()->id(),
                ]);
            }
        }
    
        // Delete the product
        $produit->delete();
    
        return response()->json(null, 204);
    }
    
}
