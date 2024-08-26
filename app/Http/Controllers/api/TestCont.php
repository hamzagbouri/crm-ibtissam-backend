<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class TestCont extends Controller
{
   
      public function login(Request $request){
          if(!Auth::attempt($request->only('email','password'))){
              return response()->json([
                  'message'=>'information de connexion non reconnu',
                  'status'=>401
              ]);
  
        
          }
          $user=User::where('email',$request->email)->firstOrFail();
          $token=$user->createToken('auth_token')->plainTextToken;
          return response()->json([
              'token'=>$token,
              'type'=>'Bearer',
              'status'=>200
             ])->cookie('jwt',$token);  
  
  
      }
      public function logout(Request $request)
      {
          $user = Auth::guard('sanctum')->user();
          $user->tokens()->delete();
          return response()->json(['message' => 'Logged out successfully'], 200);
      }
  
  
      public function user(Request $request){
          return $request->user();
      }
      
}
