<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Nov 2024 10:25:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Analytics\UserRequest\UI;

use App\Actions\Analytics\UserRequest\Traits\WithFormattedRequestLogs;
use App\Actions\Elasticsearch\BuildElasticsearchClient;
use App\Enums\Elasticsearch\ElasticsearchUserRequestTypeEnum;
use App\InertiaTable\InertiaTable;
use Closure;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsObject;

class IndexUserRequestLogs
{
    use AsObject;
    use WithFormattedRequestLogs;

    public function handle($filter = ElasticsearchUserRequestTypeEnum::VISIT->value): LengthAwarePaginator|bool|array
    {
        $client = BuildElasticsearchClient::run();

        if ($client instanceof Client) {
            try {
                $params  = [
                    'index' => config('elasticsearch.index_prefix') . 'user_requests_' . group()->slug,
                    'size'  => 10000,
                    'body'  => [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    ['match' => ['type' => $filter]],
                                     ['match' => ['type' => ElasticsearchUserRequestTypeEnum::VISIT->value]],
                                ],
                            ],
                        ],
                    ],
                ];

                return $this->format($client, $params);

            } catch (ClientResponseException $e) {
                // todo manage the 4xx error
                return false;
            } catch (ServerResponseException $e) {
                // todo manage the 5xx error
                return false;
            } catch (Exception $e) {
                // todo eg. network error like NoNodeAvailableException
                return false;
            }
        }

        return [];
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->name('vst')
                ->column(key: 'username', label: __('Username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'ip_address', label: __('IP Address'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'url', label: __('URL'), canBeHidden: false, sortable: true)
                ->column(key: 'module', label: __('Module'), canBeHidden: false, sortable: true)
                ->column(key: 'user_agent', label: __('User Agent '), canBeHidden: false, sortable: true)
                ->column(key: 'location', label: __('location'), canBeHidden: false)
                ->column(key: 'datetime', label: __('Date & Time'), canBeHidden: false, sortable: true);
        };
    }
}