<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Event extends Model
{
    const API_FIELDS = [
        "id",
        "type",
        "name",
        "cover.fields(id,source)",
        "picture.type(large)",
        "description",
        "start_time",
        "end_time",
        "category",
        "attending_count",
        "declined_count",
        "maybe_count",
        "noreply_count"
    ];

    public $id;
    public $type;
    public $name;
    public $description;
    public $startTime;
    public $endTime;
    public $place;

    function __construct($attributes)
    {
        $this->id = $attributes->id;
        $this->type = $attributes->type;
        $this->name = $attributes->name;
        $this->description = isset($attributes->description) ? $attributes->description : null;
        $this->startTime = $attributes->start_time;
        $this->endTime = isset($attributes->end_time) ? $attributes->end_time : null;
        $this->picture = isset($attributes->picture) ? $attributes->picture : null;
        $this->cover = isset($attributes->cover) ? $attributes->cover : null;
    }

    public static function randomEvent($lat, $lng, $dist, $dateTime)
    {
        $events = static::search($lat, $lng, $dist, $dateTime);
        return $events[array_rand($events)];
    }

    public static function search($lat, $lng, $dist, $dateTime)
    {
        $places = Cache::remember("places:$lat,$lng,$dist", 30, function() use ($lat, $lng, $dist) {
            return Place::search($lat, $lng, $dist);
        });

        $events = [];
        foreach ($places as $place) {
            if (empty($place->events))
                continue;
            foreach ($place->events->data as $eventData) {
                $event = new Event($eventData);

                $event->place = $place;
                array_push($events, $event);
            }
        }

        $dateTime = (new Carbon($dateTime));

        // Filter events by date
        $events = collect($events)
            ->filter(function($item) use ($dateTime) {
                $beginTime = new Carbon($item->startTime);
                $endTime = isset($item->endTime) ? new Carbon($item->endTime) : null;

                if (!$endTime)
                    return $dateTime->gt($beginTime->subHours(6)) && $dateTime->lt($beginTime->addDays(1));

                return $dateTime->gt($beginTime->subHours(6)) && $dateTime->lt($endTime);
            })
            ->toArray();

        return $events;
    }
}
