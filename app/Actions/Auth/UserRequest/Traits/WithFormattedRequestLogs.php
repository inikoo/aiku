<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 09 Jun 2023 14:05:53 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\UserRequest\Traits;

use Elastic\Elasticsearch\Client;

trait WithFormattedRequestLogs
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function format(Client $client, array $params)
    {
        try {
            $results = [];

            foreach (json_decode($client->search($params), true)['hits']['hits'] as $result) {
                $results[] = [
                    'username'      => $result['_source']['username'],
                    'ip_address'    => $result['_source']['ip_address'],
                    'location'      => json_decode($result['_source']['location'], true),
                    'device_type'   => $result['_source']['device_type'],
                    'module'        => $result['_source']['module'],
                    'platform'      => $result['_source']['platform'],
                    'browser'       => $result['_source']['browser'],
                    'route_name'    => $result['_source']['route']['name'],
                    'arguments'     => array_values($result['_source']['route']['arguments']),
                    'url'           => $result['_source']['route']['url'],
                    'datetime'      => $result['_source']['datetime']
                ];
            }

            return collect(array_reverse($results))->paginate(
                perPage: \request()->get('perPage') ?? config('ui.table.records_per_page')
            )->withQueryString();

        } catch (\Exception $e) {
            return [];
        }
    }
}
