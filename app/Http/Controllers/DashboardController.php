<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }

    public function sortTas(Request $request)
    {
        try {
            $task = User::find(auth::user()->id)->tasks()->where('date', $request->date)->get();
            if (count($task)==0) {
                return response()->json([
                    'message' => 'no task'
                ], 200);
            } else {
                $task = $task->sortBy("time");
                return response()->json($task, 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 409,
                'message' => 'Conflict',
                'description' => 'Sort Task Failed!',
                'exception' => $e
            ], 409);
        }
    }

    public function sortSchedule(Request $request)
    {
        try {
            $schedule = User::find(auth::user()->id)->schedules()->where('start_date', $request->date)->get();
            if (count($schedule)==0) {
                return response()->json([
                    'message' => 'no task'
                ], 200);
            } else {
                $schedule = $schedule->sortBy("start_time");
                return response()->json($schedule, 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 409,
                'message' => 'Conflict',
                'description' => 'Sort Schedule Failed!',
                'exception' => $e
            ], 409);
        }
    }
}
