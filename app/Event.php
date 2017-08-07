<?php

namespace App;

use GuzzleHttp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Event extends Model
{
    public static function search($lat, $lng, $dist=50000)
    {
        $locations = static::getLocations($lat, $lng, $dist);

        $locationIds = collect($locations)->map(function($item) {
            return $item->id;
        })->implode(',');

        return static::getEvents($locationIds);
    }

    private static function getEvents($locationIds)
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

        $res = $client->request('GET', 'https://graph.facebook.com/v2.10/', [
            'query' => [
                'access_token' => env('GRAPH_API_CLIENT_ID') . '|' . env('GRAPH_API_SECRET'),
                'ids' => $locationIds,
                'fields' => implode($locationFields, ',')
            ]
        ]);

        $resBody = json_decode($res->getBody()->getContents());
        return $resBody;
    }

    private static function getLocations($lat, $lng, $dist)
    {
        $locations = Cache::remember("$lat,$lng,$dist", 1, function() use ($lat, $lng, $dist) {
            $client = new GuzzleHttp\Client();
            $locations = [];
            $next = '';

            do {
                $res = empty($next)
                    ? $client->request('GET', 'https://graph.facebook.com/v2.10/search', [
                        'query' => [
                            'access_token' => env('GRAPH_API_CLIENT_ID') . '|' . env('GRAPH_API_SECRET'),
                            'type' => 'place',
                            'center' => "$lat,$lng",
                            'distance' => $dist
                        ]
                    ])
                    : $client->request('GET', $next);
                $resBody = json_decode($res->getBody()->getContents());
                $locations = array_values(array_merge($locations, $resBody->data));
                $next = isset($resBody->paging->next) ? $resBody->paging->next : '';
            } while (count($locations) < 50 && !empty($next));

            return $locations;
        });

        return $locations;
    }
}
