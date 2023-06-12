<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 09 Jun 2023 14:05:53 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Actions\Auth\User\Traits;

use Elastic\Elasticsearch\Client;

trait WithFormattedUserHistories {
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
                    'arguments'     => array_values($result['_source']['route']['arguments']),
                    'url'           => $result['_source']['route']['url'],
                    'datetime'      => $result['_source']['datetime'],
                    'type'          => $result['_source']['type'],
                    'tenant'        => app('currentTenant')->slug,
                    'old_values'    => $result['_source']['old_values'],
                    'new_values'    => $result['_source']['new_values'],
                    'event'         => $result['_source']['event'],
                    'auditable_id'   => $result['_source']['auditable_id'],
                    'auditable_type' => $result['_source']['auditable_type'],
                    'user_id'     => $result['_source']['user_id'],
                    'user_type'   => $result['_source']['user_type'],
                    'tags'        => $result['_source']['tags']
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
