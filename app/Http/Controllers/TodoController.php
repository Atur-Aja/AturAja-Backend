<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Todo;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

class TodoController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'taskId'=> 'required',
            'name'=> 'required',
        ]);

        try {
            $todo = new Todo;
            $todo->name = $request->name;
            $todo->task()->associate($request->taskId);
            $todo->save();

            return response()->json([
                "message" => "Create Todo success",
                "todo" => $todo
            ], 201);
        }catch (\Exception $e) {
            return response()->json([
                'code' => 409,
                'message' => 'Conflict',
                'description' => 'Create Todo Failed!',
                'exception' => $e
            ], 409);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $todo = Todo::find($id);
            if(!empty($todo)) {
                $todo->name = is_null($request->name) ? $todo->name : $request->name;
                $todo->status = is_null($request->status) ? $todo->status : $request->status;
                $todo->save();

                return response()->json([
                    "message" => "todo updated successfully",
                    "test" => $todo
                ], 200);
            }else {
                return response()->json([
                    "message" => "todo not found"
                ], 404);
            }
        }catch (\Exception $e) {
            return response()->json([
                'code' => 409,
                'message' => 'Conflict',
                'description' => 'update todo failed!',
                'exception' => $e
            ], 409);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $todo = Todo::find($id);
            if(!empty($todo)) {
                $todo->delete();
                return response()->json([
                    "message" => "records deleted"
                ], 202);
            }else {
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
