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
use App\Http\Traits\TimeBlockTrait;

class ScheduleController extends Controller
{
    use AuthUserTrait;
    use TimeBlockTrait;

    public function __construct()
    {
        $this->middleware('jwt.verify');
    }

    public function index()
    {
        return response()->json([
            'message' => 'you have no access',
        ], 403);
    }

    public function store(Request $request)
    {
        // Get Auth User
        $user = $this->getAuthUser();
        $userId = $user->id;

        // Validate Request
        $this->ValidateRequest();

        // Create Schedule
        try {
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

    public function show($id)
    {
        // Get Auth User
        $user = $this->getAuthUser();

        try {
            $schedule = $user->schedules()->get()->where('id', $id)->first();

            // Check ownership
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

    public function update(Request $request, $id)
    {
        // Get Auth User
        $user = $this->getAuthUser();

        // Validate Request
        $this->ValidateRequest();

        // Update Schedule
        try {
            $schedule = $user->schedules()->get()->where('id', $id)->first();
            if($schedule==null){
                return response()->json([
                    'message' => 'you have no access',
                ], 403);
            }

            $this->store($request);
            $this->destroy($schedule->id);

            return response()->json([
                'message' => 'schedule updated successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'schedule not found'
            ], 404);
        }
    }

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
                'message' => 'schedule deleted successfully',
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'schedule not found'
            ], 404);
        }
    }

    public function matchSchedule(Request $request){
        // Get Auth User
        $user = $this->getAuthUser();

        // Validate Request
        $validator = Validator::make(request()->all(), [
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'friends' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }

        $members = $request->friends;
        array_unshift($members , $user->id);

        // Check Participant exist or not
        foreach($members as $memberId){
            try {
                $member = User::findOrFail($memberId);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'message' => 'user not found',
                    'user_id' => $memberId
                ], 404);
            }
        }

        // Extract Request Body

        $startTime = $this->stringToTimeBlock($request->start_time, 15, "bawah");
        $endTime = $this->stringToTimeBlock($request->end_time, 15, "atas");

        $allFreeTimes = [];

        // Get Free Times on the date
        $scheduleArray = $this->getScheduleArray($members, $request->date);
        $freeTimes = $this->getFreeTimes($scheduleArray, $startTime, $endTime);
        foreach($freeTimes as $freeTime){
            array_unshift($freeTime , $request->date);
            array_push($allFreeTimes, $freeTime);
        }

        // If no free times, get free times on another date
        for ($i=1; $i<=3; $i++) {
            if(count($allFreeTimes)<3){
                $date = $request->date . ' +'. $i . ' day';
                $date = date('Y-m-d', strtotime($date));
                // $scheduleArray = null;
                $scheduleArray = $this->getScheduleArray($members, $date);
                $freeTimes = $this->getFreeTimes($scheduleArray, $startTime, $endTime);
                foreach($freeTimes as $freeTime){
                    array_unshift($freeTime, $date);
                    array_push($allFreeTimes, $freeTime);
                }
            }

            if(count($allFreeTimes)<3){
                $date = $request->date . ' -'. $i . ' day';
                $date = date('Y-m-d', strtotime($date));
                // $scheduleArray = null;
                $scheduleArray = $this->getScheduleArray($members, $date);
                $freeTimes = $this->getFreeTimes($scheduleArray, $startTime, $endTime);
                foreach($freeTimes as $freeTime){
                    array_unshift($freeTime, $date);
                    array_push($allFreeTimes, $freeTime);
                }
            }
        }

        // convert timeBlock to string
        $rekomendasi = [];

        for ($i=0; $i<3; $i++) {
            $date = $allFreeTimes[$i][0];
            $startTime = $this->timeBlockToString($allFreeTimes[$i][1], 15);
            $endTime = $this->timeBlockToString($allFreeTimes[$i][2], 15);
            array_push($rekomendasi, array("date"=>$date, "start_time"=>$startTime, "end_time"=>$endTime));
        }

        return response()->json([
            "rekomendasi" => $rekomendasi,
        ], 200);
    }

    public function getUserSchedule(Request $request){
        // Get Auth User
        $user = $this->getAuthUser();
        $schedules = $user->schedules()->orderBy('date')->get();

        if (count($schedules)==0) {
            return response()->json([
                "message" => "no tasks"
              ], 200);
        } else {
            foreach ($schedules as $schedule) {
                $member = Schedule::find($schedule->id)->users()->get(['users.id', 'users.username', 'users.photo']);

                $schedulesColab[] = ["schedule" => $schedule, "member" => $member];
            }

            return response()->json([
                "schedules" => $schedulesColab
            ], 200);
        }

        return $user->schedules()->get();
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
}
