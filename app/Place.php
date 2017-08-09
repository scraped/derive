<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Place extends Model
{
    const API_FIELDS = [
        "id",
        "name",
        "about",
        "emails",
        "cover.fields(id,source)",
        "picture.type(large)",
        "category",
        "category_list.fields(name)",
        "location"
    ];

    function __construct($attributes)
    {
    }

    private static function getPlaces(Collection $locationIds)
    {
        $client = new GuzzleHttp\Client();

        $eventsFields = Event::API_FIELDS;
        $locationFields = static::API_FIELDS;
        array_push($locationFields, "events.fields(" . implode($eventsFields, ',') . ")");

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

    public static function search($lat, $lng, $dist)
    {
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

        $locationIds = collect($locations)->map(function($item) {
            return $item->id;
        });

        $detailedLocations = Cache::remember("detailedLocations:$lat,$lng,$dist", 30, function() use ($locationIds) {
            return static::getPlaces($locationIds);
        });

        return $detailedLocations;
    }
}
