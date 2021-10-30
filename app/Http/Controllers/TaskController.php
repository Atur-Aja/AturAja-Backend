<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Todo;
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
        $task = User::find(auth::user()->id)->tasks()->orderBy('date')->get();
        if (count($task)==0) {
            return response()->json([
                "message" => "no tasks"
              ], 200);
        } else {
            foreach ($task as $task) {
                if(!count(Task::find($task->id)->todos()->get()) == 0){
                    $tasks[] = ["task" => $task, "todo" => Task::find($task->id)->todos()->get()];
                } else {
                    $tasks[] = ["task" => $task];
                }
            }
            return response()->json([
                "tasks" => $tasks
            ], 200);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title'=> 'required',
            'date'=> 'required',
            'time'=> 'required',
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

            //create todo
            if (empty($request->todos) == false) {
                foreach($request->todos as $value) {
                    $todo = new Todo;
                    $todo->name = $value;
                    $todo->task()->associate($task);
                    $todo->save();
                }
            }
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
            $todos = Task::find($id)->todos()->get();
            if (count($todos)==0) {
                return response($task, 200);
            } else {
                return response()->json([
                    "task" => $task,
                    "todos" => $todos,
                ], 200);
            }
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = User::find(auth::user()->id)->tasks()->get();
            $task = $task->find($id);
            if (!empty($task)) {
                $task->title = is_null($request->title) ? $task->title : $request->title;
                $task->description = is_null($request->description) ? $task->description : $request->description;
                $task->date = is_null($request->date) ? $task->date : $request->date;
                $task->time = is_null($request->time) ? $task->time : $request->time;
                $task->status = is_null($request->status) ? $task->status : $request->status;
                $task->save();

                return response()->json([
                    "message" => "task updated successfully"
                ], 200);
            } else {
                return response()->json([
                    "message" => "task not found"
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 409,
                'message' => 'Conflict',
                'description' => 'update task failed!',
                'exception' => $e
            ], 409);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find(auth::user()->id);
            $task = Task::find($id);

            if (!empty($task)) {
                $task->todos()->where('task_id', $id)->delete();
                $user->tasks()->detach($id);
                $task->delete();
                return response()->json([
                    "message" => "records deleted"
                ], 202);
            } else {
                return response()->json([
                    "message" => "task not found"
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 409,
                'message' => 'Conflict',
                'description' => 'delete task failed!',
                'exception' => $e
            ], 409);
        }
    }
}
