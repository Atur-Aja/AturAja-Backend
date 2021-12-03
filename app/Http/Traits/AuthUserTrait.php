<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Auth;

trait AuthUserTrait{
    private function getAuthUser()
    {
        try{
            return $user = auth()->userOrFail();
        }catch(\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e){
            response()->json([
                'message' => 'not authenticated, please login first'
            ], 401)->send();
            exit;
        }   
    }

    private function checkOwnership($owner){
        // Get Auth User
        $user = $this->getAuthUser(); 

        if($user->id != $owner){
            response()->json([
                'message' => 'Not Authorized',
                'user_id' => $user->id,
                'owner' => $owner
            ], 403)->send();
            exit;
        }
            
    }
}    
?>