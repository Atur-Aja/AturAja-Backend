<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Schedule::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate Request
        $this->ValidateRequest();

        // Get Auth User
        $user = $this->getAuthUser();

        // Create Schedule
        try {
            $created = $user->schedules()->create([
                'title'=>request('title'),
                'description'=>request('description'),
                'location'=>request('location'),
                'start_date'=>request('start_date'),
                'start_time'=>request('start_time'),
                'end_date'=>request('end_date'),
                'end_time'=>request('end_time'),
                'notification'=>request('notification'),
                'repeat'=>request('repeat')
            ]);
            return response()->json([                
                'message' => 'schedule created successfully',                
            ], 201);
        }
        catch(\Exception $e) {
            return response()->json([                
                'message' => 'failed to create schedule',                
            ], 409);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return Schedule::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'schedule not found'
            ], 404);
        }       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate Request
        $this->ValidateRequest();

        // Get Auth User
        $user = $this->getAuthUser();

        // Check ownership (is user who updated is who created it)
        try {
            $schedule = Schedule::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'schedule not found'
            ], 404);
        }        

        if($user->id != $schedule->user_id)
            return response()->json(['message' => 'not authorized'], 403);

        // Update Schedule
        $schedule->update([
            'title'=>request('title'),
            'description'=>request('description'),
            'location'=>request('location'),
            'start_date'=>request('start_date'),
            'start_time'=>request('start_time'),
            'end_date'=>request('end_date'),
            'end_time'=>request('end_time'),
            'notification'=>request('notification'),
            'repeat'=>request('repeat')
        ]);
        return response()->json(['message' => 'schedule updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Get Auth User
        $user = $this->getAuthUser();

        // Check ownership (is user who updated is who created it)
        try {
            $schedule = Schedule::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'schedule not found'
            ], 404);
        } 

        if($user->id != $schedule->user_id)
            return response()->json(['message' => 'not authorized'], 403);
        
        $schedule->delete();

        return response()->json(['message' => 'schedule deleted successfully'], 202);
    }

    public function getUserSchedule(Request $request, $username){
        // Get User
        try {
            $user = User::where('username', $username)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'user not found'
            ], 404);
        }        
        
        return Schedule::where('user_id', $user->id)->get();
    }

    private function ValidateRequest()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
        ]);

        if($validator->fails()) {
            response()->json($validator->messages())->send();
            exit;
        }
    }   

    private function getAuthUser()
    {
        try{
            return $user = auth('api')->userOrFail();
        }catch(\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e){
            response()->json(['message' => 'Not authenticated, please login first'], 401)->send();
            exit;
        }   
    }
}
