<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 21:34:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsObject;

class GetElasticsearchDocument
{
    use AsObject;

    public function handle(string $query): LengthAwarePaginator|bool
    {
        if ($client = BuildElasticsearchClient::run()) {
            try {
                $results = [];
                $params = [
                    'index' => config('elasticsearch.index_prefix') . 'user_requests_' . app('currentTenant')->group->slug,
                    'size' => 10000
                ];


                $params['body']['query']['match']['username'] = $query;

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
            } catch (ClientResponseException $e) {
                //dd($e->getMessage());
                // manage the 4xx error
                return false;
            } catch (ServerResponseException $e) {
                //dd($e->getMessage());
                // manage the 5xx error
                return false;
            } catch (Exception $e) {
                //dd($e->getMessage());
                // eg. network error like NoNodeAvailableException
                return false;
            }
        }

        return false;
    }

}
