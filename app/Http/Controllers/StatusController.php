<?php

namespace App\Http\Controllers;

use App\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatusController extends Controller
{
    public function store(Request $request)
    {
        $lastOrderId = Status::where('user_id', auth()->user()->id)->max('order');

        if ($lastOrderId === null) {
            $lastOrderId = 0;
        }

        $data = new Status();
        $data->title = $request->title;
        $data->slug = Str::slug($request->title);
        $data->order = $lastOrderId + 1;
        $data->user_id = auth()->user()->id;
        $data->save();
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $data = Status::find($request->statusId);
        $data->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
        ]);
        return $data;
    }

    public function all_status(Request $request)
    {
        $all_status = Status::where('user_id', auth()->user()->id)->get();
        return $all_status;
    }
}
