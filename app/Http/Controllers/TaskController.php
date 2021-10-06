<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Task;

class TaskController extends Controller
{
    public function getAll()
    {
        $task = Task::get()->toJson(JSON_PRETTY_PRINT);
        return response($task, 200);
    }

    public function getById($id)
    {
        if (Task::where('id', $id)->exists()) {
            $task = Task::where('id', $id)->get()->toJson(JSON_PRETTY_PRINT);
            return response($task, 200);
        } else {
            return response()->json([
              "message" => "task not found"
            ], 404);
          }
    }
    
    public function create(Request $request)
    {
        $this->validate($request, [
            'title'=> 'required',
            'date'=> 'required',
        ]);

        try {
            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'date' => $request->date,
                'time' => $request->time,
            ]);

            return response()->json($task, 201);

        } catch(\Exception $e) {
            return response()->json([
                'code' => 409,
                'message' => 'Conflict',
                'description' => 'Create Task Failed!',
                'exception' => $e
            ], 409);
        }
    }

    public function update(Request $request, $id)
    {
        if (Task::where('id', $id)->exists()) {
            $task = Task::find($id);
            $task->title = is_null($request->title) ? $task->title : $request->title;
            $task->description = is_null($request->description) ? $task->description : $request->description;
            $task->date = is_null($request->date) ? $task->date : $request->date;
            $task->save();

            return response()->json([
                "message" => "task updated successfully"
              ], 200);
        } else {
            return response()->json([
                "message" => "task not found"
              ], 404);
        }

    }

    public function delete($id) 
    {
        if(Task::where('id', $id)->exists()) {
          $task = Task::find($id);
          $task->delete();
  
          return response()->json([
            "message" => "records deleted"
          ], 202);
        } else {
          return response()->json([
            "message" => "task not found"
          ], 404);
        }
    }

}
