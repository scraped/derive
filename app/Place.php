<?php

namespace App;

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

    public static function getPlaces(Collection $locationIds)
    {
        $client = new GuzzleHttp\Client();

        $eventsFields = Event::API_FIELDS;
        $locationFields = static::API_FIELDS;
        array_push($locationFields, "events.fields(" . implode($eventsFields, ',') . ")");

        $detailedLocations = [];

        $chunkedIds = $locationIds->chunk(50)->toArray();

        $promises = (function () use ($chunkedIds, $client, $locationFields) {
            foreach($chunkedIds as $ids) {
                yield $client->requestAsync('GET', 'https://graph.facebook.com/v2.10/', [
                    'query' => [
                        'access_token' => static::getFbToken(),
                        'ids' => implode($ids, ','),
                        'fields' => implode($locationFields, ','),
                        'categories' => "['FOOD_BEVERAGE','ARTS_ENTERTAINMENT','FITNESS_RECREATION','SHOPPING_RETAIL']"
                    ]
                ]);
            }
        })();

        (new GuzzleHttp\Promise\EachPromise($promises, [
            'concurrency' => 5,
            'fulfilled' => function ($res) use (&$detailedLocations) {
                $resBody = (array)json_decode($res->getBody()->getContents());
                $detailedLocations = array_values(array_merge($detailedLocations, $resBody));
            }
        ]))->promise()->wait();

        return $detailedLocations;
    }

    /**
     * Get a collection of Ids of places that are within range of lat, lng, dist
     * @param $lat
     * @param $lng
     * @param $dist
     * @return mixed
     */
    public static function search($lat, $lng, $dist)
    {
        $client = new GuzzleHttp\Client();
        $places = [];

        $getNextUrl = function($next) use (&$places, $client) {
            if (empty($next))
                return null;
            $resBody = json_decode($next->getBody()->getContents());
            $places = array_values(array_merge($places, $resBody->data));
            $next = isset($resBody->paging->next) ? $resBody->paging->next : null;
            if (empty($next))
                return null;
            return $client->requestAsync('GET', $next);
        };

        return $client->requestAsync('GET', 'https://graph.facebook.com/v2.10/search', [
                'query' => [
                    'access_token' => Event::getFbToken(),
                    'type' => 'place',
                    'center' => "$lat,$lng",
                    'distance' => $dist
                ]
            ])
            ->then($getNextUrl)
            ->then($getNextUrl)
            ->then($getNextUrl)
            ->then($getNextUrl)
            ->then($getNextUrl)
            ->then($getNextUrl)
            ->then($getNextUrl)
            ->then($getNextUrl)
            ->then(function() use (&$places, $lat, $lng, $dist) {
                $placeIds = collect($places)->map(function($place) {
                        return $place->id;
                    })->unique()->toArray();
                return $placeIds;
            });
    }
}
