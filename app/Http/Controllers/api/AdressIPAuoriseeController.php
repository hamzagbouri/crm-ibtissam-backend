<?php

namespace App\Http\Controllers;

use App\Models\AdresseIPAutorisee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdresseIPAutoriseeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $adresses = AdresseIPAutorisee::all();
        return response()->json($adresses);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'addressIP' => 'required|ip',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $adresse = AdresseIPAutorisee::create($request->all());
        return response()->json($adresse, 201);
    }

    public function show($id)
    {
        $adresse = AdresseIPAutorisee::findOrFail($id);
        return response()->json($adresse);
    }

    public function update(Request $request, $id)
    {
        $adresse = AdresseIPAutorisee::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'addressIP' => 'required|ip',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $adresse->update($request->all());
        return response()->json($adresse);
    }

    public function destroy($id)
    {
        AdresseIPAutorisee::destroy($id);
        return response()->json(null, 204);
    }
}
