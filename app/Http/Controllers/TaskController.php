<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
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

    public function getUserTask()
    {
        $user = $this->getAuthUser();
        $task = $user->tasks()->orderBy('date')->get();
        if (count($task)==0) {
            return response()->json([
                "message" => "no tasks"
              ], 200);
        } else {
            foreach ($task as $task) {
                $member = Task::find($task->id)->users()->get(['users.id', 'users.username', 'users.photo']);
                if (count($member)==1) {
                    $member = null;
                }
                if(!count(Task::find($task->id)->todos()->get()) == 0){
                    $tasks[] = ["task" => $task, "todo" => Task::find($task->id)->todos()->get(), "member" => $member];
                } else {
                    $tasks[] = ["task" => $task, "member" => $member];
                }
            }

            return response()->json([
                "tasks" => $tasks
            ], 200);
        }
    }

    public function store(Request $request)
    {
        $user = $this->getAuthUser();

        $this->validate($request, [
            'title'=> 'required',
            'date'=> 'required',
            'time'=> 'required',
            'priority' => ['required', Rule::in(['0', '1', '2', '3'])],
        ]);

        try {
            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'date' => $request->date,
                'time' => $request->time,
                'priority' => $request->priority,
            ]);

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

            //add friends for collaboration
            if (empty($request->friends) == false) {
                $friends = $request->friends;
                $task->users()->attach($friends);
            }

            return response()->json([
                "message" => "create task successfully"
            ], 201);

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
        $user = $this->getAuthUser();
        $task = $user->tasks()->get()->where('id', $id);
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
            $user = $this->getAuthUser();
            $task = $user->tasks()->get();
            $task = $task->find($id);
            if (!empty($task)) {
                $task->title = is_null($request->title) ? $task->title : $request->title;
                $task->description = is_null($request->description) ? $task->description : $request->description;
                $task->date = is_null($request->date) ? $task->date : $request->date;
                $task->time = is_null($request->time) ? $task->time : $request->time;
                $task->priority = is_null($request->priority) ? $task->priority : $request->priority;
                $task->status = is_null($request->status) ? $task->status : $request->status;
                $task->save();

                $friends = $request->friends;
                $task->users()->sync($friends);

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
            $user = $this->getAuthUser();
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
