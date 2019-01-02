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

    /**
     * @var
     */
    public $id;
    /**
     * @var
     */
    public $type;
    /**
     * @var
     */
    public $name;
    /**
     * @var null|string
     */
    public $description;
    /**
     * @var
     */
    public $startTime;
    /**
     * @var null
     */
    public $endTime;
    /**
     * @var
     */
    public $place;
    /**
     * @var null
     */
    public $picture;
    /**
     * @var null
     */
    public $cover;

    /**
     * Event constructor.
     * @param $attributes
     */
    function __construct($attributes)
    {
        $this->id = $attributes->id;
        $this->type = $attributes->type;
        $this->name = $attributes->name;
        $this->description = isset($attributes->description) ? nl2br($attributes->description) : null;
        $this->startTime = $attributes->start_time;
        $this->endTime = isset($attributes->end_time) ? $attributes->end_time : null;
        $this->picture = isset($attributes->picture) ? $attributes->picture : null;
        $this->cover = isset($attributes->cover) ? $attributes->cover : null;
    }

    /**
     * @param $lat
     * @param $lng
     * @param $dist
     * @param $dateTime
     * @return mixed
     */
    public static function randomEvent($lat, $lng, $dist, $dateTime)
    {
        $events = static::search($lat, $lng, $dist, $dateTime);

        return $events[array_rand($events)];
    }

    /**
     * Search events by lat, lng, distance and time
     * Returns array of Events
     * @param double $lat Latitude
     * @param double $lng Longitude
     * @param int $dist Distance in meters
     * @param string $dateTime DateTime to filter events by
     * @return array
     */
    public static function search($lat, $lng, $dist, $dateTime = null)
    {
        $promises = (function() use ($lat, $lng, $dist) {
            $cached = Cache::get("placeIds:$lat,$lng,$dist");
            if ($cached) {
                yield new GuzzleHttp\Promise\FulfilledPromise($cached);
                return;
            }

            yield Place::search($lat, $lng, $dist);

            // approx. km to lat/lng conversion
            $d2 = ($dist+$dist/2)/111000;
            $d = [0, $d2*.7071, $d2, $d2*.7071, 0, -$d2*.7071, -$d2, -$d2*.7071];
            for ($i = 0; $i < 8; $i++) {
                yield Place::search(round($lat + $d[2], 2), round($lng + $d[0], 2), $dist);
            }
        })();

        $placeIds = [];

        (new GuzzleHttp\Promise\EachPromise($promises, [
            'concurrency' => 10,
            'fulfilled' => function($results) use (&$placeIds) {
                $placeIds = collect(array_merge($placeIds, $results))
                    ->unique()->toArray();
            },
            'rejected' => function($reason) {
                throw $reason;
            }
        ]))->promise()->wait();

        Cache::put("placeIds:$lat,$lng,$dist", $placeIds, 30);

        $places = Cache::remember("places:$lat,$lng,$dist", 30, function() use ($placeIds) {
            return Place::getPlaces(collect($placeIds));
        });

        $events = [];
        foreach ($places as $place) {
            if (empty($place->events))
                continue;
            foreach ($place->events->data as $eventData) {
                $event = new Event($eventData);

                $event->place = clone $place;
                unset($event->place->events);
                array_push($events, $event);
            }
        }

        $events = collect($events)
            ->unique('id')
            ->toArray();

        if ($dateTime != null)
            return static::filterEvents($events, new Carbon($dateTime));

        return $events;
    }

    /**
     * Filter events by date time
     * @param array $events List of Events
     * @param Carbon $dateTime DateTime to filter events by
     * @return array
     */
    public static function filterEvents($events, Carbon $dateTime)
    {
        $dateTime = new Carbon($dateTime);

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
