<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 12 Jun 2023 16:00:25 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\History;

use App\Actions\Auth\User\Traits\WithFormattedUserHistories;
use App\Actions\Elasticsearch\BuildElasticsearchClient;
use App\Enums\Elasticsearch\ElasticsearchTypeEnum;
use App\Enums\UI\TabsAbbreviationEnum;
use App\InertiaTable\InertiaTable;
use Closure;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IndexHistories
{
    use AsAction;
    use WithAttributes;
    use WithFormattedUserHistories;

    public function handle($model): LengthAwarePaginator|array|bool
    {
        $client = BuildElasticsearchClient::run();

        $auditableId = $model->id;
        $auditableType = class_basename($model);

        if ($client instanceof Client) {
            try {
                $params  = [
                    'index' => config('elasticsearch.index_prefix') . 'user_requests_' . app('currentTenant')->group->slug,
                    'size'  => 10000,
                    'body' => [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    ['match' => ['auditable_type' => $auditableType]],
                                    ['match' => ['auditable_id' => $auditableId]],
                                    ['match' => ['type' => ElasticsearchTypeEnum::ACTION->value]],
                                ],
                                'should' => [
                                    ['match' => ['user_id' => auth()->user()->id]],
                                ]
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
        return function (InertiaTable $table) {
            $table
                ->name(TabsAbbreviationEnum::HISTORY->value)
                ->pageName(TabsAbbreviationEnum::HISTORY->value.'Page')
                ->column(key: 'ip_address', label: __('IP Address'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'user_id', label: __('User ID'), canBeHidden: false, sortable: true)
                ->column(key: 'slug', label: __('Slug'), canBeHidden: false, sortable: true)
                ->column(key: 'user_name', label: __('User Name'), canBeHidden: false, sortable: true)
                ->column(key: 'url', label: __('URL'), canBeHidden: false, sortable: true)
                ->column(key: 'old_values', label: __('Old Values'), canBeHidden: false, sortable: true)
                ->column(key: 'new_values', label: __('New Values'), canBeHidden: false, sortable: true)
                ->column(key: 'event', label: __('Event'), canBeHidden: false, sortable: true)
                ->column(key: 'auditable_type', label: __('Auditable Type'), canBeHidden: false)
                ->column(key: 'auditable_id', label: __('Auditable ID'), canBeHidden: false)
                ->column(key: 'datetime', label: __('Date & Time'), canBeHidden: false, sortable: true)
                ->defaultSort('ip_address');
        };
    }
}
