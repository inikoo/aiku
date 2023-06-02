<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 26 May 2023 13:25:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

trait WithElasticsearch
{
    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    public function init(): Client
    {
        return ClientBuilder::create()->build();
    }


    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    public function getElastics(string $query): LengthAwarePaginator
    {
        $results = [];

        $params = [
            'index' => app('currentTenant')->slug,
            'size'  => 10000
        ];
        $params['body']['query']['match']['username'] = $query;

        foreach (json_decode($this->init()->search($params), true)['hits']['hits'] as $result) {
            $results[] = [
                'username'        => $result['_source']['username'],
                'ip_address'      => $result['_source']['ip_address'],
                'route_name'      => $result['_source']['route']['name'],
                'route_parameter' => array_keys($result['_source']['route']['parameters']),
                'datetime'        => $result['_source']['datetime']
            ];
        }

        return collect(array_reverse($results))->paginate(
            perPage: \request()->get('perPage') ?? config('ui.table.records_per_page')
        )->withQueryString();
    }
}
