<?php

namespace App\Http\Controllers;

use App\Http\Traits\AuthUserTrait;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function sortTas(Request $request)
    {
        try {
            $user = $this->getAuthUser();
            $tasks = $user->tasks()->where('date', $request->date)->get();
            foreach ($tasks as $task) {
                if (strtotime($task->date) == strtotime($request->date)) {
                    $taskes[] = $task;
                }
            }
            if (empty($taskes)) {
                return response()->json([
                    'message' => 'no task'
                ], 200);
            } else {
                $priority = array_column($taskes, 'priority');
                array_multisort($priority, SORT_DESC, $taskes);

                foreach ($taskes as $task) {
                    $member = Task::find($task->id)->users()->get(['users.id', 'users.username', 'users.photo']);
                    $todo = Task::find($task->id)->todos()->get();
                    if (!count($todo) == 0) {
                        $tasksTodo[] = ["task" => $task, "todo" => $todo, "member" => $member];
                    } else {
                        $tasksTodo[] = ["task" => $task, "member" => $member];
                    }
                }
                return response()->json([
                    "tasks" => $tasksTodo
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
            $user = $this->getAuthUser();
            $schedules = $user->schedules()->where('date', $request->date)->get();
            foreach ($schedules as $schedules) {
                if (strtotime($schedules->date) == strtotime($request->date)) {
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
