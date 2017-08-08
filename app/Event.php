<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Event
{
    public $id;
    public $type;
    public $name;
    public $description;
    public $startTime;
    public $endTime;
    public $place;

    private static $fbToken;

    function __construct($attributes)
    {
        $this->id = $attributes->id;
        $this->type = $attributes->type;
        $this->name = $attributes->name;
        $this->description = isset($attributes->description) ? $attributes->description : null;
        $this->startTime = $attributes->start_time;
        $this->endTime = isset($attributes->end_time) ? $attributes->end_time : null;
    }

    public static function randomEvent($lat, $lng, $dist, $dateTime)
    {
        $events = static::search($lat, $lng, $dist, $dateTime);
        return $events[array_rand($events)];
    }

    public static function search($lat, $lng, $dist, $dateTime)
    {
        $locations = static::getLocations($lat, $lng, $dist);

        $locationIds = collect($locations)->map(function($item) {
            return $item->id;
        });

        $detailedLocations = Cache::remember("detailedLocations:$lat,$lng,$dist", 30, function() use ($locationIds) {
            return static::getLocationDetails($locationIds);
        });

        $events = [];
        foreach ($detailedLocations as $loc) {
            if (empty($loc->events))
                continue;
            foreach ($loc->events->data as $eventData) {
                $event = new Event($eventData);

                $event->place = $loc;
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

    public static function getFbToken()
    {
        if (empty(static::$fbToken)) {
            static::$fbToken = env('GRAPH_API_CLIENT_ID') . '|' . env('GRAPH_API_SECRET');
        }
        return static::$fbToken;
    }

    public static function setFbToken($token)
    {
        static::$fbToken = $token;
    }

    private static function getLocationDetails(Collection $locationIds)
    {
        $client = new GuzzleHttp\Client();

        $eventsFields = [
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
        $locationFields = [
            "id",
            "name",
            "about",
            "emails",
            "cover.fields(id,source)",
            "picture.type(large)",
            "category",
            "category_list.fields(name)",
            "location",
            "events.fields(" . implode($eventsFields, ',') . ")"
        ];

        $detailedLocations = [];

        $chunkedIds = $locationIds->chunk(50)->toArray();

        foreach($chunkedIds as $ids) {
            $res = $client->request('GET', 'https://graph.facebook.com/v2.10/', [
                'query' => [
                    'access_token' => Event::getFbToken(),
                    'ids' => implode($ids, ','),
                    'fields' => implode($locationFields, ',')
                ]
            ]);
            $resBody = (array)json_decode($res->getBody()->getContents());
            $detailedLocations = array_values(array_merge($detailedLocations, $resBody));
        }

        return $detailedLocations;
    }

    private static function getLocations($lat, $lng, $dist)
    {
        $locations = Cache::remember("locations:$lat,$lng,$dist", 30, function() use ($lat, $lng, $dist) {
            $client = new GuzzleHttp\Client();
            $locations = [];
            $next = '';

            do {
                $res = empty($next)
                    ? $client->request('GET', 'https://graph.facebook.com/v2.10/search', [
                        'query' => [
                            'access_token' => Event::getFbToken(),
                            'type' => 'place',
                            'center' => "$lat,$lng",
                            'distance' => $dist
                        ]
                    ])
                    : $client->request('GET', $next);
                $resBody = json_decode($res->getBody()->getContents());
                $locations = array_values(array_merge($locations, $resBody->data));
                $next = isset($resBody->paging->next) ? $resBody->paging->next : '';
            } while (count($locations) < 500 && !empty($next));

            return $locations;
        });

        return $locations;
    }
}
