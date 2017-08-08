<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;

class EventsController extends Controller
{
    public function search(Request $request)
    {
        $this->validate($request, [
            'lat' => 'required',
            'lng' => 'required',
            'date' => 'required',
        ]);

        $result = Event::randomEvent($request->lat, $request->lng, 50000, $request->date);

        return json_encode($result);
    }
}
