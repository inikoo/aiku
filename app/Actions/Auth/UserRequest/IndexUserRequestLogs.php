<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 09 Jun 2023 13:52:18 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\UserRequest;

use App\Actions\Auth\UserRequest\Traits\WithFormattedRequestLogs;
use App\Actions\Elasticsearch\BuildElasticsearchClient;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IndexUserRequestLogs
{
    use AsObject;
    use WithFormattedRequestLogs;

    public function handle($filter = 'VISIT'): LengthAwarePaginator|bool
    {
        if ($client = BuildElasticsearchClient::run()) {
            try {
                $params  = [
                    'index' => config('elasticsearch.index_prefix') . 'user_requests_' . app('currentTenant')->group->slug,
                    'size'  => 10000,
                    'body' => [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    ['match' => ['type' => $filter]]
                                ],
                            ],
                        ],
                    ],
                ];

                return $this->format($client, $params);

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
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            }
        }

        return false;
    }

}
