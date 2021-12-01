<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskCollaboration extends Controller
{
    public function see(Request $request)
    {
        $taskId = $request->task_id;
        $member = Task::find($taskId)->users()->get(['users.id', 'users.username', 'users.photo']);
        if (count($member)==1) {
            $member = null;
        }
        return response()->json($member, 200);
    }

    public function add(Request $request)
    {
        $task = Task::find($request->task_id);
        $friends = $request->friends;
        $task->users()->attach($friends);
        return response()->json([
            "message" => "add friends collaboration successfully"
        ], 200);
    }

    public function remove(Request $request)
    {
        $task = Task::find($request->task_id);
        $friends = $request->friends;
        $task->users()->detach($friends);
        return response()->json([
            "message" => "add friends collaboration successfully"
        ], 200);
    }

    public function update(Request $request)
    {
        $task = Task::find($request->task_id);
        $friends = $request->friends;
        $task->users()->sync($friends);
        return response()->json([
            "message" => "update friends collaboration successfully"
        ], 200);
    }
}
