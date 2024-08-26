<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\TestCont;
use App\Http\Controllers\ClientController;
 use App\Http\Controllers\MatierePremiereController;
 use App\Http\Controllers\movementController;
 use App\Http\Controllers\produitController;
 use App\Http\Controllers\CommandeController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [TestCont::class, 'login']);

Route::middleware('auth:sanctum')->post('logout', [TestCont::class, 'logout']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('client', [ClientController::class, 'index']);
    Route::post('client/add', [ClientController::class, 'store']);
    Route::put('client/update/{id}', [ClientController::class, 'update']);
    Route::delete('client/remove/{id}', [ClientController::class, 'destroy']);

    Route::get('matiere', [MatierePremiereController::class, 'index']);
    Route::get('matiere/{id}', [MatierePremiereController::class, 'show']);

    Route::put('matiere/update/{id}', [MatierePremiereController::class, 'update']);
    Route::delete('matiere/remove/{id}', [MatierePremiereController::class, 'destroy']);
    Route::post('matiere/add', [MatierePremiereController::class, 'store']);

    Route::get('movement', [movementController::class, 'index']);
    Route::get('produit', [produitController::class, 'index']);
    Route::get('produit/{id}', [produitController::class, 'show']);
    Route::post('produit/add', [produitController::class, 'store']);
    Route::put('produit/update/{id}', [produitController::class, 'update']);
    Route::delete('produit/remove/{id}', [produitController::class, 'destroy']);

    Route::get('commande', [CommandeController::class, 'index']);

   
   
    Route::post('commande/add', [CommandeController::class, 'store']);

    Route::put('commande/update/{id}', [CommandeController::class, 'update']);
    Route::delete('commande/remove/{id}', [CommandeController::class, 'destroy']);
    Route::get('user', [TestCont::class, 'user']);








   


});

