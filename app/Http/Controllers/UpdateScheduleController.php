<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateScheduleController extends Controller
{
    public function perbarui(Request $request, $id)
    {
        // Get Auth User


        // Validate Request
//        $this->ValidateRequest();

        // Update Schedule
        try {
            $user = $this->getAuthUser();
            $schedule = $user->schedules()->get();
            $schedule = $schedule->find($id);

            if (!empty($schedule)) {
                $schedule->title = is_null($request->title) ? $schedule->title : $request->title;
                $schedule->description = is_null($request->description) ? $schedule->description : $request->description;
                $schedule->location = is_null($request->location) ? $schedule->location : $request->location;
                $schedule->date = is_null($request->date) ? $schedule->date : $request->date;
                $schedule->start_time = is_null($request->start_time) ? $schedule->start_time : $request->start_time;
                $schedule->end_time = is_null($request->end_time) ? $schedule->end_time : $request->end_time;
                $schedule->notification = is_null($request->notification) ? $schedule->notification : $request->notification;
                $schedule->repeat = is_null($request->repeat) ? $schedule->repeat : $request->repeat;
                $schedule->save();

                $friends = $request->friends;
                $schedule->users()->sync($friends);

                return response()->json([
                    "message" => "task updated successfully"
                ], 200);
            } else {
                return response()->json([
                    "message" => "task not found"
                ], 404);
            }

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
}
