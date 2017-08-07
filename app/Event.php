<?php

namespace App;

use GuzzleHttp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Event extends Model
{
    public static function search($lat, $lng, $dist=15)
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

        $locationIds = collect($locations)->map(function($item) {
            return $item->id;
        })->implode(',');

        return $locationIds;
    }
}
