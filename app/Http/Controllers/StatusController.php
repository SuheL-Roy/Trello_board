<?php

namespace App\Http\Controllers;

use App\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatusController extends Controller
{
    public function store(Request $request)
    {

        $lastOrder = Status::orderBy('id', 'desc')->first();

        if ($lastOrder) {
            $lastOrderId = $lastOrder->order;
        } else {
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
}
