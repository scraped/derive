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

    public static function getPlaces(Collection $locationIds)
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
        $locations = [];

        $resolvePromise = function($promise) {
            return $promise->then(function($val) use (&$resolvePromise) {
                if ($val instanceof GuzzleHttp\Promise\Promise)
                    return $resolvePromise($val);
                return $val;
            });
        };

        $makeRequest = function($url) use ($client, &$makeRequest, &$locations) {
            return $client->requestAsync('GET', $url)
                ->then(function($val) use (&$locations, $client, &$makeRequest) {
                    $resBody = json_decode($val->getBody()->getContents());
                    $locations = array_values(array_merge($locations, $resBody->data));
                    $next = isset($resBody->paging->next) ? $resBody->paging->next : '';

                    if (empty($next))
                        return $locations;

                    return $makeRequest($next);
                });
        };

        $promise =
            $client->requestAsync('GET', 'https://graph.facebook.com/v2.10/search', [
                'query' => [
                    'access_token' => Event::getFbToken(),
                    'type' => 'place',
                    'center' => "$lat,$lng",
                    'distance' => $dist
                ]
            ]);

        $promise->then(function($val) use (&$locations, $client, &$makeRequest) {
            $resBody = json_decode($val->getBody()->getContents());
            $locations = array_values(array_merge($locations, $resBody->data));
            $next = isset($resBody->paging->next) ? $resBody->paging->next : '';

            if (empty($next))
                return $locations;

            return $makeRequest($next);
        });

        $resolvePromise($promise)
            ->wait();

        $locationIds = collect($locations)->map(function($item) {
            return $item->id;
        });

        return $locationIds;
    }
}
