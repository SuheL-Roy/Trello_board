<?php

namespace App\Http\Controllers;

use App\Status;
use App\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = auth()->user()->statuses()->where('track', null)->with('tasks')->get();

        return view('tasks.index', compact('tasks'));
    }

    public function all_task()
    {
        $tasks = auth()->user()->statuses()->where('track', null)->with('tasks')->get();
        return $tasks;
    }

    public function id_wise_task(Request $request)
    {
        $data = Task::find($request->taskId);
        return $data;
    }

    public function remove(Request $request)
    {
        $data = Task::find($request->taskId);
        $data->delete();
        return $data;
    }

    public function task_update(Request $request)
    {
        $data = Task::find($request->taskId);
        $data->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return $data;
    }

    public function status_update(Request $request)
    {
        $data = Status::find($request->status_id);

        $data->track = 1;
        $data->save();

        return response()->json($data, 200);
    }

    /*------ Archive Board Undo -------*/

    public function undo_update(Request $request)
    {
        $data = Status::find($request->status_id);

        $data->track = null;
        $data->save();

        return response()->json($data, 200);
    }
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'string', 'max:56'],
            'description' => ['nullable', 'string'],
            'status_id' => ['required', 'exists:statuses,id']
        ]);

        return $request->user()
            ->tasks()
            ->create($request->only('title', 'description', 'status_id'));
    }

    /*------ After Drag and drop card list position update -------*/


    public function sync(Request $request)
    {
        $this->validate(request(), [
            'columns' => ['required', 'array']
        ]);

        foreach ($request->columns as $status) {
            foreach ($status['tasks'] as $i => $task) {
                $order = $i + 1;
                if ($task['status_id'] !== $status['id'] || $task['order'] !== $order) {
                    request()->user()->tasks()
                        ->find($task['id'])
                        ->update(['status_id' => $status['id'], 'order' => $order]);
                }
            }
        }

        return $request->user()->statuses()->with('tasks')->get();
    }


    /*------ Board Drag and Drop and position update and after page reload Board position not reset -----*/


    public function status(Request $request)
    {
        $this->validate(request(), [
            'columns' => ['required', 'array']
        ]);


        foreach ($request->columns as $index => $status) {
            $card = Status::find($status['id']);
            if ($card) {
                $card->order = $index + 1;
                $card->save();
            }
        }

        return $request->user()->statuses()->get();
    }

    public function status_wise_task(Request $request)
    {
        $data = Task::where('status_id', $request->tack_id)->update(['status_id' => $request->board_id]);
        return $data;
    }

    public function show(Task $task)
    {
        //
    }

    public function edit(Task $task)
    {
        //
    }

    public function update(Request $request, Task $task)
    {
        //
    }

    public function destroy(Task $task)
    {
        //
    }
}
