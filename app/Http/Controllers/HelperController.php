<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class HelperController extends Controller
{
    public function cekUser(Request $request){
        // Validate Request
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:1|max:16',
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }

        if(User::where('username', '=', $request->username)->exists()){
            return response()->json([
                'isAvailable' => 'false',
            ], 200);
        }else{
            return response()->json([
                'isAvailable' => 'true',
            ], 200);
        };
    }
}
