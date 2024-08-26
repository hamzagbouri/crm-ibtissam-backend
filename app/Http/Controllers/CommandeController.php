<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\ProduitFini;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommandeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $commandes = Commande::with('client')->get(); // Adjust as needed
        return response()->json($commandes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'commandeID' => 'required|string|unique:commandes,commandeID',
            'dateCommande' => 'required|date',
            'dateLivraison' => 'nullable|date',
            'status' => 'required|string|max:255',
            'produits' => 'required|array',
            'produits.*.id' => 'required|exists:produit_finis,id',
            'produits.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prixTotal = 0;

        foreach ($request->produits as $produitData) {
            $produit = ProduitFini::find($produitData['id']);

            if ($produit->quantity < $produitData['quantity']) {
                return response()->json(['error' => 'Insufficient quantity for ' . $produit->name], 422);
            }

            $prixTotal += $produit->priceU * $produitData['quantity'];

            $produit->quantity -= $produitData['quantity'];
            $produit->save();

            MouvementStock::create([
                'type' => 'sortie',
                'date' => now(),
                'quantity' => $produitData['quantity'],
                'produit_id' => $produit->id,
                'user_id' => auth()->id(),
            ]);
        }

        $commande = Commande::create(array_merge(
            $request->only(['client_id', 'commandeID', 'dateCommande', 'dateLivraison', 'status']),
            ['prixTotal' => $prixTotal, 'produits' => $request->produits]
        ));

        return response()->json($commande, 201);
    }

    public function show($id)
    {
        $commande = Commande::with('client')->findOrFail($id); // Adjust as needed
        return response()->json($commande);
    }

    public function update(Request $request, $id)
    {
        $commande = Commande::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'client_id' => 'sometimes|required|exists:clients,id',
            'commandeID' => 'sometimes|required|string|unique:commandes,commandeID,' . $id,
            'dateCommande' => 'sometimes|required|date',
            'dateLivraison' => 'nullable|date',
            'status' => 'sometimes|required|string|max:255',
            'produits' => 'sometimes|array',
            'produits.*.id' => 'required|exists:produit_finis,id',
            'produits.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldProduits = $commande->produits;
        $commande->update($request->only(['client_id', 'commandeID', 'dateCommande', 'dateLivraison', 'status']));

        $prixTotal = 0;

        // Handle products that were in the old command
        foreach ($oldProduits as $oldProduit) {
            $produit = ProduitFini::find($oldProduit['id']);
            $produit->quantity += $oldProduit['quantity'];
            $produit->save();

            MouvementStock::create([
                'type' => 'entre',
                'date' => now(),
                'quantity' => $oldProduit['quantity'],
                'produit_id' => $produit->id,
                'user_id' => auth()->id(),
            ]);
        }

        // Handle new products
        foreach ($request->produits as $produitData) {
            $produit = ProduitFini::find($produitData['id']);

            if ($produit->quantity < $produitData['quantity']) {
                return response()->json(['error' => 'Insufficient quantity for ' . $produit->name], 422);
            }

            $prixTotal += $produit->price * $produitData['quantity'];

            $produit->quantity -= $produitData['quantity'];
            $produit->save();

            MouvementStock::create([
                'type' => 'sortie',
                'date' => now(),
                'quantity' => $produitData['quantity'],
                'produit_id' => $produit->id,
                'user_id' => auth()->id(),
            ]);
        }

        $commande->update(['prixTotal' => $prixTotal]);

        return response()->json($commande);
    }

    public function destroy($id)
    {
        $commande = Commande::findOrFail($id);

        foreach ($commande->produits as $produitData) {
            $produit = ProduitFini::find($produitData['id']);
            $produit->quantity += $produitData['quantity'];
            $produit->save();

            MouvementStock::create([
                'type' => 'entre',
                'date' => now(),
                'quantity' => $produitData['quantity'],
                'produit_id' => $produit->id,
                'user_id' => auth()->id(),
            ]);
        }

        $commande->delete();

        return response()->json(null, 204);
    }
}
