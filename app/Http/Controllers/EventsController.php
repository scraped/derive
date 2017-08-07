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
            'lng' => 'required'
        ]);

        $results = Event::search($request->lat, $request->lng);
        dd($results);

        return Event::search($request->lat, $request->lng);
    }
}
