<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Task;
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
                foreach ($task as $task) {
                    $taskes[] = $task;
                }
                $priority = array_column($taskes, 'priority');
                array_multisort($priority, SORT_DESC, $taskes);
                foreach ($taskes as $task) {
                    $member = Task::find($task->id)->users()->get(['users.id', 'users.username', 'users.photo']);
                    $todo = Task::find($task->id)->todos()->get();
                    if (!count($todo) == 0) {
                        $tasks[] = ["task" => $task, "todo" => $todo, "member" => $member];
                    } else {
                        $tasks[] = ["task" => $task, "member" => $member];
                    }
                }
                return response()->json([
                    "tasks" => $tasks
                ], 200);
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
            $schedules = User::find(auth::user()->id)->schedules()->get();
            foreach ($schedules as $schedules) {
                if (strtotime($schedules->start_date) <= strtotime($request->date) && strtotime($schedules->end_date) >= strtotime($request->date)) {
                    $schedule[] = $schedules;
                }
            }
            if (empty($schedule)) {
                return response()->json([
                    'message' => 'no schedule'
                ], 200);
            } else {
                $start_time = array_column($schedule, 'start_time');
                array_multisort($start_time, SORT_ASC, $schedule);

                foreach ($schedule as $schedule) {
                    $member = Schedule::find($schedule->id)->users()->get(['users.id', 'users.username', 'users.photo']);
                    if (count($member)==1) {
                        $member = null;
                    }
                    $scheduleMember[] = ["schedule" => $schedule, "member" => $member];
                }
                return response()->json($scheduleMember, 200);
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
