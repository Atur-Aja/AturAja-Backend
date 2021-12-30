<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Schedule;
use App\Http\Traits\AuthUserTrait;

class UpdateScheduleController extends Controller
{
    use AuthUserTrait;

    public function __construct()
    {
        $this->middleware('jwt.verify');
    }
    
    public function perbarui(Request $request, $id)
    {
        // Get Auth User
        $user = $this->getAuthUser();
        $userId = $user->id;

        // Validate Request
        $this->ValidateRequest();

        // Update Schedule
        try {
            $schedule = $user->schedules()->get()->where('id', $id)->first();
            if($schedule==null){
                return response()->json([
                    'message' => 'schedule not found',
                ], 404);
            }

            // Create Schedule            
            $schedule = $this->createSchedule($request, $request->date, $userId);
            $date = $schedule->date;

            if(strcmp(request('repeat'), 'daily') == 0){
                for ($x = 0; $x < 7; $x++) {
                    $date = date('Y-m-d', strtotime($schedule->date . ' +1 day'));
                    $schedule = $this->createSchedule($request, $date, $userId);
                }
            }else if(strcmp(request('repeat'), 'weekly') == 0){
                for ($x = 0; $x < 4; $x++) {
                    $date = date('Y-m-d', strtotime($schedule->date . ' +1 week'));
                    $schedule = $this->createSchedule($request, $date, $userId);
                }
            }else if(strcmp(request('repeat'), 'monthly') == 0){
                for ($x = 0; $x < 6; $x++) {
                    $date = date('Y-m-d', strtotime($schedule->date . ' +1 month'));
                    $schedule = $this->createSchedule($request, $date, $userId);
                }
            }                
            

            // Destroy schedule            
            $schedule = $user->schedules()->get()->where('id', $id)->first();

            if($schedule==null){
                return response()->json([
                    'message' => 'you have no access',
                ], 403);
            }

            $schedules = $user->schedules()
                        ->where('schedules.title', $schedule->title)
                        ->where('schedules.repeat', $schedule->repeat)
                        ->where('schedules.updated_at', $schedule->updated_at)
                        ->get(['schedules.id']);

            foreach ($schedules as $schedule){
                $schedule->users()->detach();
                $schedule->delete();
            }        

            return response()->json([
                'message' => 'schedule updated successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'schedule not found'
            ], 404);
        }
    }

    private function ValidateRequest()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|string|min:3|max:32',
            'description' => 'max:128',
            'location' => 'max:128',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'repeat' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'never'])],
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

    private function createSchedule(Request $request, $date, $userId){
        $schedule = Schedule::create([
            'title'=>request('title'),
            'description'=>request('description'),
            'location'=>request('location'),
            'date'=>$date,
            'start_time'=>request('start_time'),
            'end_time'=>request('end_time'),
            'notification'=>request('notification'),
            'repeat'=>request('repeat')
        ]);

        $schedule->users()->attach($userId);
        if($request->has('friends')){
            $schedule->users()->attach(request('friends'));
        }

        return $schedule;
    }
}
