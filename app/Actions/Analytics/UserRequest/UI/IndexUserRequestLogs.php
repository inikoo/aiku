<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Nov 2024 10:25:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Analytics\UserRequest\UI;

use App\Actions\Analytics\UserRequest\Traits\WithFormattedRequestLogs;
use App\Actions\Elasticsearch\BuildElasticsearchClient;
use App\Actions\GrpAction;
use App\Actions\SysAdmin\User\WithUsersSubNavigation;
use App\Actions\UI\Grp\SysAdmin\ShowSysAdminDashboard;
use App\Enums\Elasticsearch\ElasticsearchUserRequestTypeEnum;
use App\Http\Resources\SysAdmin\UserRequestLogsResource;
use App\InertiaTable\InertiaTable;
use Closure;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsObject;

class IndexUserRequestLogs extends GrpAction
{
    use AsObject;
    use WithFormattedRequestLogs;
    use WithUsersSubNavigation;

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

    public function htmlResponse(LengthAwarePaginator $requests, ActionRequest $request): Response
    {
        $subNavigation = $this->getUsersNavigation($this->group, $request);
        return Inertia::render(
            'SysAdmin/UserRequests',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('User Requests'),
                'pageHead'    => [
                    'title'   => __('User Requests'),
                    'subNavigation' => $subNavigation,
                ],
                'data'        => UserRequestLogsResource::collection($requests),
            ]
        )->table($this->tableStructure());
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

    public function asController(ActionRequest $request)
    {
        $group = group();
        $this->initialisation($group, $request);
        return $this->handle();
    }

    public function getBreadcrumbs($suffix = null): array
    {
        return array_merge(
            ShowSysAdminDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.sysadmin.users.request.index',
                        ],
                        'label' => __('User Requests'),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }
}
