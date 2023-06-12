<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 08 Jun 2023 14:34:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Auth\User\Traits\WithFormattedUserHistories;
use App\Actions\Elasticsearch\BuildElasticsearchClient;
use App\Enums\UI\TabsAbbreviationEnum;
use App\InertiaTable\InertiaTable;
use Closure;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IndexUserHistories
{
    use AsAction;
    use WithAttributes;
    use WithFormattedUserHistories;

    public function handle(string $query = null, $filter = 'ACTION'): LengthAwarePaginator|array|bool
    {
        $client = BuildElasticsearchClient::run();

        if ($client instanceof Client) {
            try {
                $params  = [
                    'index' => config('elasticsearch.index_prefix') . 'user_requests_' . app('currentTenant')->group->slug,
                    'size'  => 10000,
                    'body' => [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    ['match' => ['username' => $query]],
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

        return [];
    }
    public function tableStructure(?array $modelOperations = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations) {
            $table
                ->name(TabsAbbreviationEnum::HISTORY->value)
                ->pageName(TabsAbbreviationEnum::HISTORY->value.'Page')
                ->column(key: 'username', label: __('Username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'ip_address', label: __('IP Address'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'url', label: __('URL'), canBeHidden: false, sortable: true)
                ->column(key: 'old_values', label: __('Old Values'), canBeHidden: false, sortable: true)
                ->column(key: 'new_values', label: __('New Values'), canBeHidden: false, sortable: true)
                ->column(key: 'event', label: __('Event'), canBeHidden: false, sortable: true)
                ->column(key: 'auditable_type', label: __('Auditable Type'), canBeHidden: false)
                ->column(key: 'datetime', label: __('Date & Time'), canBeHidden: false, sortable: true)
                ->defaultSort('username');
        };
    }
}
