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
            'date' => 'required'
        ]);

        if (!empty($request->fbToken)) {
            Event::setFbToken($request->fbToken);
        }

        $result = [];
        try {
            $result = Event::randomEvent($request->lat, $request->lng, 12500, $request->date);
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            if (str_contains($exception->getMessage(), 'exceeded the rate limit'))
                return json_encode(['error' => 'Rate limit exceeded. Log in to avoid public rate limit or try again later.']);
        }

        return json_encode($result);
    }
}
