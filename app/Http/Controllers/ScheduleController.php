<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'message' => 'you have no access',
        ], 403);
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
            $schedule = Schedule::create([
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

            $schedule->users()->attach(Auth::user()->id);
            if($request->has('friends')){
                $schedule->users()->attach(request('friends'));
            }

            return response()->json([
                'message' => 'schedule created successfully',
            ], 201);
        }
        catch(\Exception $e) {
            return response()->json([
                'message' => 'failed to create schedule',
                'error' => $e,
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
        // Get Auth User
        $user = $this->getAuthUser();

        // Check ownership
        try {
            $schedule = $user->schedules()->get()->where('id', $id)->first();
            if($schedule==null){
                return response()->json([
                    'message' => 'you have no access',
                ], 403);
            } else {
                return response()->json($schedule, 200);
            }
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

        // Update Schedule
        try {
            $schedule = $user->schedules()->get()->where('id', $id)->first();
            if($schedule==null){
                return response()->json([
                    'message' => 'you have no access',
                ], 403);
            } else {
                return response()->json($schedule, 200);
            }

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

            if($request->has('friends')){
                $schedule->users()->sync(request('friends'));
            }
            $schedule->users()->attach(Auth::user()->id);

            return response()->json([
                'message' => 'schedule updated successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'schedule not found'
            ], 404);
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
        // Get Auth User
        $user = $this->getAuthUser();

        try {
            $schedule = $user->schedules()->get()->where('id', $id)->first();
            if($schedule==null){
                return response()->json([
                    'message' => 'you have no access',
                ], 403);
            } else {
                return response()->json($schedule, 200);
            }

            $schedule->users()->detach();
            $schedule->delete();
            return response()->json([
                'message' => 'schedule deleted successfully'
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'schedule not found'
            ], 404);
        }
    }

    public function getUserSchedule(Request $request){
        // Get Auth User
        $user = $this->getAuthUser();
        return $user->schedules()->get();
    }

    private function ValidateRequest()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|string|min:3|max:32',
            'description' => 'max:128',
            'location' => 'max:128',
            'start_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
            'end_time' => 'required|date_format:H:i|after:start_time',
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
            response()->json([
                'message' => 'Not authenticated, please login first'
            ], 401)->send();
            exit;
        }
    }
}
