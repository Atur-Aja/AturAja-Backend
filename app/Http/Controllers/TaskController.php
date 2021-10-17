<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
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
        return response()->json(Task::all());
    }

    public function getUserTask(Request $request, $username)
    {
        // Get User
        try {
            $user = User::where('username', $username)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'user not found'
            ], 404);
        }   
        
        $task = User::find($user->id)->tasks()->orderBy('title')->get();
        if (count($task)==0) {
            return response()->json([
                "message" => "no tasks"
              ], 200);
        } else {
            return response($task, 200);
        }
    }

    public function store(Request $request)
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

            $user = User::find(Auth::user()->id);
            $task->users()->attach($user);

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

    public function show($id)
    {
        $task = User::find(auth::user()->id)->tasks()->get()->where('id', $id);
        if (count($task)==0) {
            return response()->json([
                "message" => "task not found"
              ], 404);
        } else {
            return response($task, 200);
        }         
    }
    
    public function update(Request $request, $id)
    {
        $task = User::find(auth::user()->id)->tasks()->get()->where('id', $id);
        if (!count($task) == 0) {
            $task[0]->title = is_null($request->title) ? $task[0]->title : $request->title;
            $task[0]->description = is_null($request->description) ? $task[0]->description : $request->description;
            $task[0]->date = is_null($request->date) ? $task[0]->date : $request->date;
            $task[0]->save();

            return response()->json([
                "message" => "task updated successfully",
                "test" => $task[0]
              ], 200);
        } else {
            return response()->json([
                "message" => "task not found"
              ], 404);
        }

    }

    public function destroy($id) 
    {
        $task = User::find(auth::user()->id)->tasks()->get()->where('id', $id);
        if(!count($task) == 0) {
            User::find(auth::user()->id)->tasks()->where('id', $id)->detach();
  
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
