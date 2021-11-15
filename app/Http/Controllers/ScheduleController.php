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

    public function matchSchedule(Request $request){
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

        // Get Auth User
        $user = $this->getAuthUser();       

        $scheduleArray = [];

        // Get All Participant Schedule
        foreach($request->friends as $friendsId){
            try {
                $participant = User::findOrFail($friendsId);                             
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'message' => 'user not found'
                ], 404);
            }

            $schedules = $participant->schedules()->get();

            // Get All Schedule on that date
            foreach ($schedules as $schedule) {
                if (strtotime($schedule->start_date) <= strtotime($request->date) 
                    && strtotime($schedule->end_date) >= strtotime($request->date)) {
                    
                    // Convert time string to time block
                    $startTime = $this->stringToTimeBlock($schedule->start_time, 15, "bawah");
                    $endTime = $this->stringToTimeBlock($schedule->end_time, 15, "atas");
                    array_push($scheduleArray, [$startTime, $endTime]);
                }
            }
        }

        // Get Free Times
        $startTime = $this->stringToTimeBlock($request->start_time, 15, "bawah");
        $endTime = $this->stringToTimeBlock($request->end_time, 15, "atas");
        $freeTimes = $this->getFreeTimes($scheduleArray, $startTime, $endTime);

        // convert timeBlock to string
        $rekomendasi = [];
        for ($i = 0; $i < count($freeTimes); $i++) {
            $startTime = $this->timeBlockToString($freeTimes[$i][0], 15);
            $endTime = $this->timeBlockToString($freeTimes[$i][1], 15);
            array_push($rekomendasi, array("start_time"=>$startTime, "end_time"=>$endTime));
        }

        return response()->json([
            "rekomendasi" => $rekomendasi,
        ], 200);
    }    

    public function getUserSchedule(Request $request){
        // Get Auth User
        $user = $this->getAuthUser();
        $schedules = $user->schedules()->orderBy('start_date')->get();

        if (count($schedules)==0) {
            return response()->json([
                "message" => "no tasks"
              ], 200);
        } else {
            foreach ($schedules as $schedule) {
                $member = Schedule::find($schedule->id)->users()->get(['users.id', 'users.username', 'users.photo']);
                if (count($member)==1) {
                    $member = null;
                }
                $schedulesColab[] = ["schedule" => $schedule, "member" => $member];
            }

            return response()->json([
                "schedules" => $schedulesColab
            ], 200);
        }

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
            'end_date' => 'required|date_format:Y-m-d',
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

    private function stringToTimeBlock($time, $duration=15, $batas="bawah"){
		$time = strtotime ($time) - strtotime("today") - 60; //Get Timestamp
		$duration = $duration * 60;

		// Pembulatan Kebawah
		$selisih = $time % $duration;
		if($selisih!=0){
			if($batas=="bawah"){
				$time = $time - $selisih;
			}elseif($batas=="atas"){
				$time = $time + ($duration - $selisih);
			}
		}
		
		$time = $time/$duration;
		return $time;
	}

	private function timeBlockToString($time, $duration=15){
		$time = $time * $duration;
		$hours = floor($time / 60);
   		$minutes = ($time % 60);

        if($minutes<10){
            $minutes = "0" . $minutes;
        }
        if($hours<10){
            $hours = "0" . $hours;
        }

		$timeString = $hours . ":" . $minutes;
		
		return $timeString;
	}

    private function getFreeTimes($schedules, $startTime, $endTime){
        $scheduleTimeLength = $endTime - $startTime;
        $timeTable = array_fill(0, 96, FALSE);

        for ($i = 0; $i < count($schedules); $i++) {
            for ($j = $schedules[$i][0]; $j <= $schedules[$i][1]; $j++) {
                $timeTable[$j] = TRUE;
            }
        }
        
        $freeTimes = $this->checkFreeTimes($timeTable, $endTime, 96, $scheduleTimeLength);
        if(count($freeTimes)<=0){
            $freeTimes = $this->checkFreeTimes($timeTable, 0, $startTime, $scheduleTimeLength);
        }
        return $freeTimes;
    }

    private function checkFreeTimes($timeTable, $batasBawah, $batasAtas, $scheduleTimeLength){
        $freeTimes = [];
        $counter = 0;
        $startFreeTime = -1;    
    
        for ($i = $batasBawah; $i < $batasAtas; $i++) {
            if($timeTable[$i]){
                // simpan timeblock kosong
                if($startFreeTime!=-1 && $counter>=$scheduleTimeLength){
                    $endFreeTime = $startFreeTime + $counter;
                    $freeTime = [$startFreeTime, $endFreeTime];
                    array_push($freeTimes, $freeTime);
                }
                
                $counter = 0;
                $startFreeTime = -1;

            }else{
                // hitung timeblock kosong
                if($startFreeTime==-1 && $counter==0){
                    $startFreeTime = $i;
                    
                //simpan timeblock kosong
                }else if($startFreeTime!=-1 && $counter>=$scheduleTimeLength){
                    $endFreeTime = $startFreeTime + $counter;
                    $freeTime = [$startFreeTime, $endFreeTime];
                    array_push($freeTimes, $freeTime);
                    $counter = 0;
                    $startFreeTime = $i;
                }

                $counter++;
            }
        }
    
        // simpan timeblock kosong
        if($startFreeTime!=0 && $counter>=$scheduleTimeLength){
            $endFreeTime = $startFreeTime + $counter;
            $freeTime = [$startFreeTime, $endFreeTime];
            array_push($freeTimes, $freeTime);
        }

        if(count($freeTimes)>3){
            return (array_slice($freeTimes,0,3));
        }else{
            return $freeTimes;
        }
    }
}