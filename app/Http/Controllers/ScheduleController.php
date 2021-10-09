<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Schedule;

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
        $created = $user->schedules()->create([
            'title'=>request('title'),
            'description'=>request('description'),
            'location'=>request('location'),
            'start_time'=>request('start_time'),
            'end_time'=>request('end_time'),
            'notification'=>request('notification'),
            'repeat'=>request('repeat')
        ]);

        return response()->json($created, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (Schedule::where('id', $id)->exists()) {
        //     $schedule = Schedule::where('id', $id)->get()->toJson(JSON_PRETTY_PRINT);
        //     return response($schedule, 200);
        // } else {
        //     response()->json(['message' => 'schedule not found'], 404);
        // }
        return Schedule::find($id);
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

        if (Schedule::where('id', $id)->exists()) {
            $schedule = Schedule::find($id);

            // Update Schedule
            $schedule->update([
                'title'=>request('title'),
                'description'=>request('description'),
                'location'=>request('location'),
                'start_time'=>request('start_time'),
                'end_time'=>request('end_time'),
                'notification'=>request('notification'),
                'repeat'=>request('repeat')
            ]);
            return response()->json(['message' => 'schedule updated successfully'], 200);
        } else {
            response()->json(['message' => 'schedule not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Schedule::where('id', $id)->exists()) {
            $schedule = Schedule::find($id);
            $schedule->delete();
    
            return response()->json(['message' => 'schedule deleted successfully'], 202);
          } else {
            response()->json(['message' => 'schedule not found'], 404);
          }
    }

    private function ValidateRequest()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'start_time' => 'required',
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
            response()->json(['message' => 'not authenticated, please login first'])->send();
            exit;
        }   
    }
}
