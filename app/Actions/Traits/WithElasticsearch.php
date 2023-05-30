<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 26 May 2023 13:25:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Actions\Traits;

use App\Models\Auth\User;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait WithElasticsearch
{
    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    public function init(): Client
    {
        return ClientBuilder::create()->build();
    }

    public function storeElastic(Request $request, ?User $user): void
    {
        $data =  [
            'datetime' => now(),
            'tenant_slug' => app('currentTenant')->slug,
            'username' => $user->username,
            'route' => [
                'name' => $request->route()->getName(),
                'parameters' => $request->route()->parameters
            ],
            'ip_address' => $request->ip()
        ];

        $this->init()->index([
            'index' => $data['tenant_slug'],
            'body' => $data
        ]);
    }

    public function getElastics(string $query): LengthAwarePaginator
    {
        $results = [];

        $params = [
            'index' => app('currentTenant')->slug,
            'size' => 10000
        ];
        $params['body']['query']['match']['username'] = $query;

        foreach (json_decode($this->init()->search($params), true)['hits']['hits'] as $result) {
            $results[] = [
                'username' => $result['_source']['username'],
                'ip_address' => $result['_source']['ip_address'],
                'route_name' => $result['_source']['route']['name'],
                'route_parameter' => array_keys($result['_source']['route']['parameters']),
                'datetime' => $result['_source']['datetime']
            ];
        }

        return collect(array_reverse($results))->paginate();
    }
}
